<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class CommunitiesController extends AppController
{
    /**
     * Alters $this->paginate settings according to $_GET and Cookie data,
     * and remembers $_GET data with a cookie.
     */
    private function adminIndexFilter()
    {
        $cookieParentKey = 'AdminCommunityIndex';

        // Remember selected filters
        $this->filters = $this->request->query('filters');
        if (is_array($this->filters)) {
            foreach ($this->filters as $group => $filter) {
                $this->Cookie->write("$cookieParentKey.filters.$group", $filter);
            }
        } else {
            $this->filters = [];
        }

        // Use remembered filters when no filters manually specified
        foreach (['progress', 'track'] as $group) {
            if (! isset($this->filters[$group])) {
                $key = "$cookieParentKey.filters.$group";
                if ($this->Cookie->check($key)) {
                    $this->filters[$group] = $this->Cookie->read($key);
                }
            }
        }

        // Default filters if completely unspecified
        if (! isset($this->filters['progress'])) {
            $this->filters['progress'] = 'ongoing';
        }

        // Apply filters
        foreach ($this->filters as $filter) {
            switch ($filter) {
                case 'ongoing':
                    $this->paginate['conditions']['Communities.score <'] = '5';
                    break;
                case 'completed':
                    $this->paginate['conditions']['Communities.score'] = '5';
                    break;
                case 'fast_track':
                    $this->paginate['conditions']['Communities.fast_track'] = true;
                    break;
                case 'normal_track':
                    $this->paginate['conditions']['Communities.fast_track'] = false;
                    break;
                case 'all':
                default:
                    // No action
                    break;
            }
        }
    }

    private function adminIndexSetupFilterButtons()
    {
        $allFilters = [
            'progress' => [
                'all' => 'All',
                'completed' => 'Completed',
                'ongoing' => 'Ongoing'
            ],
            'track' => [
                'all' => 'All',
                'fast_track' => 'Fast Track',
                'normal_track' => 'Normal Track'
            ]
        ];
        $this->filters = $this->request->query('filters');
        if (! is_array($this->filters)) {
            $this->filters = [];
        }
        foreach ($this->filters as $group => $filter) {
            if ($filter == 'all') {
                unset($this->filters[$group]);
            }
        }
        $buttons = [];
        foreach ($allFilters as $group => $filters) {
            $groupLabel = ucwords($group);
            $selectedFilterKey = isset($this->filters[$group]) ?
                $this->filters[$group]
                : null;
            if ($selectedFilterKey != 'all') {
                $selectedFilterLabel = isset($filters[$selectedFilterKey]) ?
                    $filters[$selectedFilterKey]
                    : null;
                if ($selectedFilterLabel) {
                    $groupLabel .= ': <strong>'.$selectedFilterLabel.'</strong>';
                }
            }

            $links = [];
            foreach ($filters as $filter => $label) {
                // Only show 'all' link if filter is active
                if ($filter == 'all' && ! isset($this->filters[$group])) {
                    continue;
                }

                // Don't show links to active filters
                if (isset($this->filters[$group]) && $this->filters[$group] == $filter) {
                    continue;
                }

                $linkFilters = [$group => $filter];
                $linkFilters = array_merge($this->filters, $linkFilters);
                $links[$label] = $linkFilters;
            }

            $buttons[$groupLabel] = $links;
        }
        $this->set('buttons', $buttons);
    }

    /**
     * Used by admin_add and admin_edit
     * @param string $role
     * @return array An array of error messages
     */
    private function processNewAssociatedUsers($role)
    {
        $dataKey = "new_{$role}s";
        if (! isset($this->request->data[$dataKey])) {
            return [];
        }

        $retval = [];
        $usersTable = TableRegistry::get('Users');
        foreach ($this->request->data[$dataKey] as $newUser) {
            $user = $usersTable->newEntity($newUser);
            $user->role = $role;

            if ($user->errors()) {
                foreach ($user->errors() as $field => $error) {
                    $retval[] = $error;
                }
                continue;
            }

            if ($usersTable->save($user)) {
                if ($usersTable->sendNewAccountEmail($user, $newUser['password'])) {
                    $this->request->data["{$role}s"][] = $user->id;
                } else {
                    $retval[] = 'There was an error emailing account login info to '.$newUser['name'].' No new account was created. Please contact an administrator for assistance.';
                    $usersTable->delete($user);
                }
            } else {
                $retval[] = 'There was an error creating an account for '.$newUser['name'].' Please contact an administrator for assistance.';
            }
        }
        return $retval;
    }

    /**
     * Used by admin_add and admin_edit
     * @param int|null $communityId
     * @return array An array of error messages
     */
    private function validateClients($communityId = null)
    {
        if (! isset($this->request->data['clients'])) {
            return [];
        }

        $retval = [];
        $usersTable = TableRegistry::get('Users');
        foreach ($this->request->data['clients'] as $client) {
            $associatedCommunityId = $this->Communities->getClientCommunityId($client['id']);
            if ($associatedCommunityId && $associatedCommunityId != $communityId) {
                $community = $this->Communities->get($associatedCommunityId);
                $user = $usersTable->get($client['id']);
                $retval[] = $user->name.' is already the client for '.$community->name;
            }
        }
        return $retval;
    }

    /**
     * Passes necessary variables the view to be used by the adding/editing form
     * @param Entity $community
     */
    private function prepareForm($community)
    {
        if ($this->request->action == 'add' && ! $this->request->is(['post', 'put'])) {
            $community->public = false;
            $community->score = 0;
        }

        $surveysTable = TableRegistry::get('Surveys');
        $areasTable = TableRegistry::get('Areas');
        $this->set([
            'areas' => $areasTable->find('list'),
            'community' => $community
        ]);
    }

    private function validateForm($community)
    {
        $communityErrors = $community->errors();
        $clientErrors = array_merge(
            $this->processNewAssociatedUsers('client'),
            $this->validateClients($community->id)
        );
        $consultantErrors = $this->processNewAssociatedUsers('consultant');
        $this->set(compact('clientErrors', 'consultantErrors'));
        return empty($communityErrors)
            && empty($clientErrors)
            && empty($consultantErrors);
    }

    public function index()
    {
        if (isset($_GET['search'])) {
            $this->paginate['conditions']['Communities.name LIKE'] = '%'.$_GET['search'].'%';
        } else {
            $this->adminIndexFilter();
        }
        $this->cookieSort('AdminCommunityIndex');
        $this->paginate['finder'] = 'adminIndex';
        $this->paginate['sortWhitelist'] = ['Communities.name', 'Area.name'];
        $this->adminIndexSetupFilterButtons();
        $this->set([
            'communities' => $this->paginate()->toArray(),
            'titleForLayout' => 'Indiana Communities'
        ]);
    }

    public function add()
    {
        $community = $this->Communities->newEntity();

        if ($this->request->is('post')) {
            if (! $this->request->data['meeting_date_set']) {
                $this->request->data['town_meeting_date'] = null;
            }

            $community = $this->Communities->patchEntity($community, $this->request->data(), [
                'associated' => ['OfficialSurvey', 'OrganizationSurvey']
            ]);

            $validates = $this->validateForm($community);
            if ($validates) {
                if ($this->Communities->save($community)) {
                    $url = Router::url(['action' => 'addClient', $community->id]);
                    $message = $community->name.' has been added.';
                    $message .= '<br />Now you can <a href="'.$url.'">create a client account</a> for this community';
                    $this->Flash->success($message);
                    return $this->redirect([
                        'prefix' => 'admin',
                        'action' => 'index'
                    ]);
                }
            }
        }

        $this->prepareForm($community);
        $this->set('titleForLayout', 'Add Community');
        $this->render('form');
    }

    public function edit($communityId = null)
    {
        if (! $communityId) {
            throw new NotFoundException('Community ID not specified');
        }

        $community = $this->Communities->get($communityId, [
            'contain' => [
                'OfficialSurvey',
                'OrganizationSurvey'
            ]
        ]);

        if ($this->request->is('post') || $this->request->is('put')) {
            $this->request->data['id'] = $communityId;

            if (! $this->request->data['meeting_date_set']) {
                $this->request->data['town_meeting_date'] = null;
            }

            $community = $this->Communities->patchEntity($community, $this->request->data(), [
                'associated' => ['OfficialSurvey', 'OrganizationSurvey']
            ]);
            $validates = $this->validateForm($community);
            if ($validates && $this->Communities->save($community)) {
                $this->Flash->success('Community updated');
                return $this->redirect([
                    'prefix' => 'admin',
                    'action' => 'index'
                ]);
            }
        }

        $this->prepareForm($community);
        $this->set([
            'communityId' => $communityId,
            'titleForLayout' => 'Edit '.$community->name
        ]);
        $this->render('form');
    }

    public function delete($communityId = null)
    {
        if (! $this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        if (! $this->Communities->exists(['id' => $communityId])) {
            throw new NotFoundException('Invalid community');
        }
        $community = $this->Communities->get($communityId);
        if ($this->Communities->delete($community)) {
            $this->Flash->success('Community deleted');
        } else {
            $this->Flash->error('There was an error deleting that community');
        }
        return $this->redirect($this->request->referer());
    }

    public function clients($communityId)
    {
        $community = $this->Communities->find('all')
            ->select(['id', 'name'])
            ->where(['id' => $communityId])
            ->contain([
                'Clients' => function ($q) {
                    return $q
                        ->select(['name', 'email'])
                        ->order(['name' => 'ASC']);
                }
            ])
            ->first();
        if (! $community) {
            throw new NotFoundException('Sorry, we couldn\'t find a community with ID# '.$communityId);
        }
        $this->set([
            'titleForLayout' => $community->name.' Clients',
            'clients' => $community->clients,
            'communityId' => $communityId
        ]);
    }

    public function progress($communityId)
    {
        if (! $this->Communities->exists(['id' => $communityId])) {
            throw new NotFoundException('Sorry, we couldn\'t find a community with ID# '.$communityId);
        }

        $community = $this->Communities->get($communityId);
        $previousScore = $community->score;

        if ($this->request->is('put')) {
            $community = $this->Communities->patchEntity($community, $this->request->data(), [
                'fieldList' => ['score']
            ]);
            if ($community->dirty('score')) {
                if ($this->Communities->save($community)) {
                    $verbed = $community->score > $previousScore ? 'increased' : 'decreased';
                    $this->Flash->success('Community score '.$verbed);
                } else {
                    $this->Flash->error('There was an error updating this community');
                }
            } else {
                $this->Flash->notification('Score not changed');
            }
        }

        $this->set([
            'titleForLayout' => $community->name.' Progress',
            'community' => $community,
            'criteria' => $this->Communities->getProgress($communityId, true),
            'fastTrack' => $community->fast_track
        ]);
    }

    public function spreadsheet()
    {
        if (isset($_GET['search'])) {
            $this->paginate['conditions']['Communities.name LIKE'] = '%'.$_GET['search'].'%';
        } else {
            $this->adminIndexFilter();
        }
        $this->cookieSort('AdminCommunityIndex');
        $this->paginate['finder'] = 'adminIndex';
        $this->paginate['sortWhitelist'] = ['Communities.name', 'Area.name'];
        $this->adminIndexSetupFilterButtons();

        $communities = $this->paginate()->toArray();

        if (! isset($_GET['debug'])) {
            $this->response->type(['excel2007' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
            $this->response->type('excel2007');
            $this->response->download('CRI Overview.xlsx');
            $this->viewBuilder()->layout('spreadsheet');
        }
        $this->set([
            'objPHPExcel' => $this->Communities->getSpreadsheetObject($communities),
            'communities' => $communities
        ]);
    }

    public function clienthome($communityId)
    {
        $this->Cookie->write('communityId', $communityId);
        $clientId = $this->Communities->getCommunityClientId($communityId);
        $this->Cookie->write('clientId', $clientId);
        return $this->redirect([
            'prefix' => 'client',
            'controller' => 'Communities',
            'action' => 'index'
        ]);
    }

    public function addClient($communityId)
    {
        $community = $this->Communities->get($communityId);
        $usersTable = TableRegistry::get('Users');

        if ($this->request->is('post')) {
            $client = $usersTable->newEntity($this->request->data());
            $client->role = 'client';
            $client->client_communities = [$this->Communities->get($communityId)];
            $client->password = $this->request->data('unhashed_password');
            $errors = $client->errors();
            if (empty($errors) && $usersTable->save($client)) {
                if ($usersTable->sendNewAccountEmail($client, $this->request->data('password'))) {
                    $this->Flash->success('Client account created for '.$client->name.' and login instructions emailed');
                    return $this->redirect(['action' => 'clients', $communityId]);
                } else {
                    $retval[] = 'There was an error emailing account login info to '.$client->name.' No new account was created. Please contact an administrator for assistance.';
                    $usersTable->delete($client);
                }
            } else {
                $this->Flash->error('There was an error saving that client. Please try again or contact an administrator for assistance.');
            }
        } else {
            $client = $usersTable->newEntity();
        }

        $this->set([
            'client' => $client,
            'communityId' => $communityId,
            'communityName' => $community->name,
            'randomPassword' => $usersTable->generatePassword(),
            'role' => 'client',
            'titleForLayout' => 'Add a New Client for '.$community->name,
        ]);
    }

    public function selectClient($communityId)
    {
        $community = $this->Communities->get($communityId);
        $usersTable = TableRegistry::get('Users');

        if ($this->request->is('post')) {
            $clientId = $this->request->data('client_id');
            $client = $usersTable->get($clientId, ['contain' => ['ClientCommunities']]);
            $alreadyLinked = false;

            // Unlink client from any other community
            if (! empty($client['client_communities'])) {
                foreach ($client['client_communities'] as $linkedCommunity) {
                    if ($linkedCommunity['id'] == $communityId) {
                        $alreadyLinked = true;
                        continue;
                    }
                    $communityEntity = $this->Communities->get($linkedCommunity['id']);
                    $usersTable->ClientCommunities->unlink($client, [$communityEntity]);
                    $this->Flash->notification($client->name.'\'s association with '.$linkedCommunity['name'].' has been removed');
                }
            }

            // Link client with this community
            if ($alreadyLinked) {
                $this->Flash->notification($client->name.' is already assigned to '.$community->name);
                return $this->redirect(['action' => 'clients', $communityId]);
            } elseif ($this->Communities->Clients->link($community, [$client])) {
                $this->Flash->success($client->name.' is now assigned to '.$community->name);
                return $this->redirect(['action' => 'clients', $communityId]);
            } else {
                $this->Flash->error('There was an error assigning '.$client->name.' to '.$community->name);
            }
        } else {
            $client = $usersTable->newEntity();
        }

        $this->set([
            'client' => $client,
            'clients' => $usersTable->getClientList(),
            'communityId' => $communityId,
            'communityName' => $community->name,
            'titleForLayout' => 'Add a New Client for '.$community->name
        ]);
    }
}
