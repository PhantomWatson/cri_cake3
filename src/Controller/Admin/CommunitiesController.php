<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Mailer\Mailer;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Hash;

class CommunitiesController extends AppController
{

    public $paginate = [
        'order' => ['Communities.name' => 'ASC']
    ];

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
     * Passes necessary variables the view to be used by the adding/editing form
     *
     * @param Entity $community
     * @return void
     */
    private function prepareForm($community)
    {
        if ($this->request->action == 'add' && ! $this->request->is(['post', 'put'])) {
            $community->public = false;
            $community->score = 0;
        }

        $areasTable = TableRegistry::get('Areas');
        $areas = $areasTable->getGroupedList();
        $areaTypes = array_keys($areas);
        $this->set(compact('areas', 'areaTypes', 'community'));
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        if (isset($_GET['search'])) {
            $this->paginate['conditions']['Communities.name LIKE'] = '%'.$_GET['search'].'%';
        } else {
            $this->adminIndexFilter();
        }
        $this->cookieSort('AdminCommunityIndex');
        $this->paginate['finder'] = 'adminIndex';
        $this->paginate['sortWhitelist'] = ['Communities.name', 'ParentAreas.name'];
        $this->adminIndexSetupFilterButtons();
        $this->prepareAdminHeader();
        $this->set([
            'communities' => $this->paginate()->toArray(),
            'titleForLayout' => 'Indiana Communities'
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null
     */
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

            $errors = $community->errors();
            if (empty($errors)) {
                $community = $this->Communities->save($community);
                $clientUrl = Router::url([
                    'action' => 'addClient',
                    $community->id
                ]);
                $surveyUrl = Router::url([
                    'prefix' => 'admin',
                    'controller' => 'Surveys',
                    'action' => 'link',
                    $community->id,
                    'official'
                ]);
                $message = $community->name.' has been added.';
                $message .= '<br />Now you can <a href="'.$surveyUrl.'">set up this community\'s first questionnaire</a> and then <a href="'.$clientUrl.'">create a client account</a> for this community.';
                $this->Flash->success($message);
                return $this->redirect([
                    'prefix' => 'admin',
                    'action' => 'index'
                ]);
            }
        } else {
            $this->request->data['score'] = 1;
            $settingsTable = TableRegistry::get('Settings');
            $this->request->data['intAlignmentAdjustment'] = $settingsTable->getIntAlignmentAdjustment();
            $this->request->data['intAlignmentThreshhold'] = $settingsTable->getIntAlignmentThreshhold();
        }

        $this->prepareForm($community);
        $this->prepareAdminHeader();
        $this->set('titleForLayout', 'Add Community');
        $this->render('form');
    }

    /**
     * Edit method
     *
     * @param int|null $communityId Community ID
     * @return \Cake\Network\Response|null
     */
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
            $errors = $community->errors();
            if (empty($errors) && $this->Communities->save($community)) {
                $this->Flash->success('Community updated');
                return $this->redirect([
                    'prefix' => 'admin',
                    'action' => 'index'
                ]);
            }
        }

        $this->prepareForm($community);
        $this->prepareAdminHeader();
        $this->set([
            'communityId' => $communityId,
            'titleForLayout' => 'Edit ' . $community->name
        ]);
        $this->render('form');
    }

    /**
     * Delete method
     *
     * @param int|null $communityId Community ID
     * @return \Cake\Network\Response|null
     */
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

    /**
     * Clients method
     *
     * @param int $communityId Community ID
     * @return void
     */
    public function clients($communityId)
    {
        $community = $this->Communities->find('all')
            ->select(['id', 'name'])
            ->where(['id' => $communityId])
            ->contain([
                'Clients' => function ($q) {
                    return $q->order(['name' => 'ASC']);
                },
                'Surveys' => function ($q) {
                    return $q->where(['type' => 'official']);
                }
            ])
            ->first();
        if (! $community) {
            throw new NotFoundException('Sorry, we couldn\'t find a community with ID# ' . $communityId);
        }
        $this->prepareAdminHeader();
        $this->set([
            'community' => $community,
            'titleForLayout' => $community->name . ' Clients'
        ]);
    }

    /**
     * Progress method
     *
     * @param int $communityId Community ID
     */
    public function progress($communityId)
    {
        if (! $this->Communities->exists(['id' => $communityId])) {
            throw new NotFoundException('Sorry, we couldn\'t find a community with ID# ' . $communityId);
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
                    $this->Flash->success('Community score ' . $verbed);
                } else {
                    $this->Flash->error('There was an error updating this community');
                }
            } else {
                $this->Flash->notification('Score not changed');
            }
        }

        $this->prepareAdminHeader();
        $this->set([
            'titleForLayout' => $community->name . ' Progress',
            'community' => $community,
            'criteria' => $this->Communities->getProgress($communityId, true),
            'fastTrack' => $community->fast_track
        ]);
    }

    /**
     * Spreadsheet method
     *
     * @return void
     */
    public function spreadsheet()
    {
        if (isset($_GET['search'])) {
            $this->paginate['conditions']['Communities.name LIKE'] = '%' . $_GET['search'] . '%';
        } else {
            $this->adminIndexFilter();
        }
        $this->cookieSort('AdminCommunityIndex');
        $this->paginate['finder'] = 'adminIndex';
        $this->paginate['sortWhitelist'] = ['Communities.name', 'ParentAreas.name'];
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

    /**
     * Client home method
     *
     * @param int $communityId Community ID
     * @return \Cake\Network\Response|null
     */
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

    /**
     * Add client method
     *
     * @param int $communityId Community ID
     * @return \Cake\Network\Response|null
     */
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
                $Mailer = new Mailer();
                $result = $Mailer->sendNewAccountEmail(
                    $client,
                    $this->request->data('unhashed_password')
                );
                if ($result) {
                    $msg = 'Client account created for ' . $client->name . ' and login instructions emailed';
                    $this->Flash->success($msg);
                    return $this->redirect(['action' => 'clients', $communityId]);
                } else {
                    $msg = 'There was an error emailing account login info to ' . $client->name . '.';
                    $msg .= ' No new account was created. Please contact an administrator for assistance.';
                    $retval[] = $msg;
                    $usersTable->delete($client);
                }
            } else {
                $msg = 'There was an error saving that client.';
                $msg .= ' Please try again or contact an administrator for assistance.';
                $this->Flash->error($msg);
            }
        } else {
            $client = $usersTable->newEntity();
            $client->unhashed_password = $usersTable->generatePassword();
        }

        $this->set([
            'client' => $client,
            'communityId' => $communityId,
            'communityName' => $community->name,
            'salutations' => $usersTable->getSalutations(),
            'role' => 'client',
            'titleForLayout' => 'Add a New Client for ' . $community->name,
        ]);
    }

    /**
     * Remove client method
     *
     * @param int $clientId Client user ID
     * @param int $communityId Community ID
     * @return \Cake\Network\Response|null
     */
    public function removeClient($clientId, $communityId)
    {
        $usersTable = TableRegistry::get('Users');
        $client = $usersTable->get($clientId);
        $community = $this->Communities->get($communityId);
        $this->Communities->Clients->unlink($community, [$client]);
        $msg = 'Removed ' . $client->name . ' from ' . $community->name;
        $this->Flash->success($msg);
        return $this->redirect($this->referer());
    }

    /**
     * Select client method
     *
     * @param int $communityId Community ID
     * @return \Cake\Network\Response|null
     */
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
                    $msg = $client->name . '\'s association with ' . $linkedCommunity['name'] . ' has been removed';
                    $this->Flash->notification($msg);
                }
            }

            // Link client with this community
            if ($alreadyLinked) {
                $this->Flash->notification($client->name . ' is already assigned to ' . $community->name);
                return $this->redirect(['action' => 'clients', $communityId]);
            } elseif ($this->Communities->Clients->link($community, [$client])) {
                $this->Flash->success($client->name . ' is now assigned to ' . $community->name);
                return $this->redirect(['action' => 'clients', $communityId]);
            } else {
                $this->Flash->error('There was an error assigning ' . $client->name . ' to ' . $community->name);
            }
        } else {
            $client = $usersTable->newEntity();
        }

        $this->set([
            'client' => $client,
            'clients' => $usersTable->getClientList(),
            'communityId' => $communityId,
            'communityName' => $community->name,
            'titleForLayout' => 'Add a New Client for ' . $community->name
        ]);
    }

    /**
     * Alignment calculation settings method
     *
     * @return void
     */
    public function alignmentCalcSettings()
    {
        $settingsTable = TableRegistry::get('Settings');
        $settings = $settingsTable->find('all')
            ->select(['name', 'value'])
            ->where(function ($exp, $q) {
                return $exp->in('name', ['intAlignmentAdjustment', 'intAlignmentThreshhold']);
            })
            ->toArray();
        $settings = Hash::combine($settings, '{n}.name', '{n}.value');
        $communities = $this->Communities->find('all')
            ->select(['id', 'name', 'intAlignmentAdjustment', 'intAlignmentThreshhold'])
            ->order(['created' => 'DESC']);
        $this->set([
            'communities' => $communities,
            'settings' => $settings,
            'titleForLayout' => 'Internal Alignment Calculation Settings'
        ]);
    }

    /**
     * Method for /admin/communities/reports
     *
     * @return void
     */
    public function reports()
    {
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        $report = $this->Communities->getReport();

        $this->set([
            'report' => $report,
            'sectors' => $sectors,
            'titleForLayout' => 'CRI Admin Report: All Communities'
        ]);
    }


    /**
     * Method for /admin/communities/reports
     *
     * @return void
     */
    public function reportOcra()
    {
        if (! isset($_GET['debug'])) {
            $this->response->type(['excel2007' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
            $this->response->type('excel2007');
            $date = date('M-d-Y');
            $this->response->download("CRI Report - OCRA - $date.xlsx");
            $this->viewBuilder()->layout('spreadsheet');
        }
        $objPHPExcel = null;
        $this->set([
            'objPHPExcel' => $objPHPExcel
        ]);
    }
}
