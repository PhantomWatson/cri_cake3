<?php
namespace App\Controller\Admin;

use App\AdminToDo\AdminToDo;
use App\Controller\AppController;
use App\Mailer\Mailer;
use App\Model\Entity\Community;
use App\Model\Table\ProductsTable;
use Cake\Event\Event;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Hash;

class CommunitiesController extends AppController
{

    public $paginate = [
        'order' => ['Communities.name' => 'ASC'],
        'contain' => ['OptOuts']
    ];
    public $filters = [];

    /**
     * Alters $this->paginate settings according to $_GET and Cookie data,
     * and remembers $_GET data with a cookie.
     *
     * @return void
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
        $filterTypes = ['progress']; // More may be added later
        foreach ($filterTypes as $group) {
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
                case 'all':
                default:
                    // No action
                    break;
            }
        }
    }

    /**
     * Sets the $buttons variable for the view
     *
     * @return void
     */
    private function adminIndexSetupFilterButtons()
    {
        $allFilters = [
            'progress' => [
                'all' => 'All',
                'completed' => 'Completed',
                'ongoing' => 'Ongoing'
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
                    $groupLabel .= ': <strong>' . $selectedFilterLabel . '</strong>';
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
     * @param Entity $community Community
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
            $this->paginate['conditions']['Communities.name LIKE'] = '%' . $_GET['search'] . '%';
        } else {
            $this->adminIndexFilter();
        }
        $this->cookieSort('AdminCommunityIndex');
        $this->paginate['finder'] = 'adminIndex';
        $this->paginate['sortWhitelist'] = ['Communities.name', 'ParentAreas.name'];
        $communities = $this->paginate()->toArray();
        $communities = $this->addSurveyStatuses($communities);

        $this->adminIndexSetupFilterButtons();
        $this->set([
            'communities' => $communities,
            'titleForLayout' => 'Indiana Communities'
        ]);
    }

    /**
     * Adds the field $community[$surveyType]['status'] to each community in the provided array
     *
     * @param array $communities Array of Community entities
     * @return array
     */
    private function addSurveyStatuses($communities)
    {
        foreach ($communities as &$community) {
            foreach (['official_survey', 'organization_survey'] as $surveyType) {
                if (! isset($community[$surveyType])) {
                    $community[$surveyType] = [];
                }
                $community[$surveyType]['status'] = $this->getSurveyStatus($community, $surveyType);
            }
        }

        return $communities;
    }

    /**
     * Adds the field $community[$surveyType]['status']
     *
     * @param Community $community Community entity
     * @param string $surveyType 'official_survey' or 'organization_survey'
     * @return string
     */
    private function getSurveyStatus($community, $surveyType)
    {
        if ($community['opt_outs']) {
            foreach ($community['opt_outs'] as $optOut) {
                $relevantProductId = $surveyType == 'official_survey'
                    ? ProductsTable::OFFICIALS_SURVEY
                    : ProductsTable::ORGANIZATIONS_SURVEY;
                if ($optOut['product_id'] == $relevantProductId) {
                    return 'Opted out';
                }
            }
        }

        if (! isset($community[$surveyType]['sm_id'])) {
            return 'Not set up';
        }

        $currentStep = floor($community['score']);
        $stepForSurvey = $surveyType == 'official_survey' ? 2 : 3;
        $active = $community[$surveyType]['active'];
        if ($currentStep == $stepForSurvey) {
            return $active ? 'In progress' : 'Being finalized';
        }

        if ($currentStep < $stepForSurvey) {
            return $active ? 'Activated early' : 'Ready';
        }

        return $active ? 'Ready to deactivate' : 'Complete';
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null
     */
    public function add()
    {
        $community = $this->Communities->newEntity();

        if ($this->request->is('post')) {
            $community = $this->Communities->patchEntity($community, $this->request->data(), [
                'associated' => ['OfficialSurvey', 'OrganizationSurvey']
            ]);

            $errors = $community->errors();
            if (empty($errors)) {
                $community = $this->Communities->save($community);

                // Set flash message
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
                $message =
                    $community->name . ' has been added.' .
                    '<br />Now you can <a href="' . $surveyUrl . '">set up this community\'s first questionnaire</a> ' .
                    'and then <a href="' . $clientUrl . '">create a client account</a> for this community.';
                $this->Flash->success($message);

                // Dispatch event
                $event = new Event('Model.Community.afterAdd', $this, ['meta' => [
                    'communityId' => $community->id
                ]]);
                $this->eventManager()->dispatch($event);

                return $this->redirect([
                    'prefix' => 'admin',
                    'action' => 'index'
                ]);
            }
        } else {
            $this->request->data['score'] = 1;
            $settingsTable = TableRegistry::get('Settings');
            $this->request->data['intAlignmentAdjustment'] = $settingsTable->getIntAlignmentAdjustment();
            $this->request->data['intAlignmentThreshold'] = $settingsTable->getIntAlignmentThreshold();
        }

        $this->prepareForm($community);
        $this->set('titleForLayout', 'Add Community');
        $this->render('form');
    }

    /**
     * Edit method
     *
     * @param int|null $communityId Community ID
     * @return \Cake\Http\Response|null
     * @throws NotFoundException
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
        $previousScore = $community->score;

        if ($this->request->is('post') || $this->request->is('put')) {
            $this->request->data['id'] = $communityId;

            $community = $this->Communities->patchEntity($community, $this->request->data(), [
                'associated' => ['OfficialSurvey', 'OrganizationSurvey']
            ]);
            $areaUpdated = $community->dirty('local_area_id') || $community->dirty('parent_area_id');
            $errors = $community->errors();

            if (empty($errors) && $this->Communities->save($community)) {
                $this->Flash->success('Community updated');

                // Dispatch events
                if ($previousScore != $community->score) {
                    $this->dispatchScoreChangeEvent($previousScore, $community->score, $communityId);
                }
                if ($areaUpdated) {
                    $event = new Event('Model.Community.afterUpdateCommunityArea', $this, ['meta' => [
                        'communityId' => $communityId
                    ]]);
                    $this->eventManager()->dispatch($event);
                }

                return $this->redirect([
                    'prefix' => 'admin',
                    'action' => 'index'
                ]);
            }
        }

        $this->prepareForm($community);
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
     * @return \Cake\Http\Response|null
     * @throws MethodNotAllowedException
     * @throws NotFoundException
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

            // Dispatch event
            $event = new Event('Model.Community.afterDelete', $this, ['meta' => [
                'communityId' => $communityId,
                'communityName' => $community->name
            ]]);
            $this->eventManager()->dispatch($event);
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
        $this->set([
            'community' => $community,
            'titleForLayout' => $community->name . ' Clients'
        ]);
    }

    /**
     * Progress method
     *
     * @param int $communityId Community ID
     * @return void
     * @throws NotFoundException
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
                    $this->dispatchScoreChangeEvent($previousScore, $community->score, $communityId);
                } else {
                    $this->Flash->error('There was an error updating this community');
                }
            } else {
                $this->Flash->notification('Score not changed');
            }
        }

        $this->set([
            'titleForLayout' => $community->name . ' Progress',
            'community' => $community,
            'criteria' => $this->Communities->getProgress($communityId, true)
        ]);
    }

    /**
     * Client home method
     *
     * @param int $communityId Community ID
     * @return \Cake\Http\Response|null
     */
    public function clienthome($communityId)
    {
        $this->Cookie->write('communityId', $communityId);
        $this->loadComponent('ClientHome');
        $prepResult = $this->ClientHome->prepareClientHome($communityId);
        if ($prepResult) {
            return $this->render('/Client/Communities/index');
        }

        $this->Flash->error('That client home page is currently unavailable.');

        return $this->redirect([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'index'
        ]);
    }

    /**
     * Add client method
     *
     * @param int $communityId Community ID
     * @return \Cake\Http\Response|null
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
     * @return \Cake\Http\Response|null
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
     * @return \Cake\Http\Response|null
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
                return $exp->in('name', ['intAlignmentAdjustment', 'intAlignmentThreshold']);
            })
            ->toArray();
        $settings = Hash::combine($settings, '{n}.name', '{n}.value');
        $conditions = [];
        $includeDummy = (bool)$this->request->query('show-dummy');
        if (! $includeDummy) {
            $conditions['dummy'] = 0;
        }
        $communities = $this->Communities->find('all')
            ->select(['id', 'name', 'intAlignmentAdjustment', 'intAlignmentThreshold'])
            ->where($conditions)
            ->order(['created' => 'DESC']);

        $surveysTable = TableRegistry::get('Surveys');
        $avgIntAlignment = $surveysTable->getAvgIntAlignment($includeDummy);

        $this->set([
            'avgIntAlignment' => $avgIntAlignment,
            'communities' => $communities,
            'settings' => $settings,
            'titleForLayout' => 'Internal Alignment Calculation Settings'
        ]);
    }

    /**
     * Method for /admin/communities/presentations
     *
     * @param null|int $communityId Community ID
     * @return \Cake\Http\Response|null
     * @throws NotFoundException
     */
    public function presentations($communityId = null)
    {
        if (! $communityId) {
            throw new NotFoundException('Community ID not specified');
        }

        $community = $this->Communities->get($communityId);
        $community = $this->Communities->patchEntity($community, $this->request->getData());
        $optOutsTable = TableRegistry::get('OptOuts');
        if ($this->request->is('post') || $this->request->is('put')) {
            foreach (['a', 'b', 'c', 'd'] as $letter) {
                $selection = $this->request->getData('presentation_' . $letter . '_scheduled');
                if ($selection == 'opted-out') {
                    $addResult = $optOutsTable->addOptOut([
                        'user_id' => $this->Auth->user('id'),
                        'community_id' => $communityId,
                        'presentation_letter' => $letter
                    ]);
                    if (! $addResult) {
                        $msg = 'There was an error opting this community out of Presentation ' .
                            strtoupper($letter);
                        $this->Flash->error($msg);
                    }
                    $community->{'presentation_' . $letter} = null;
                } elseif ($selection == 0) {
                    $community->{'presentation_' . $letter} = null;
                }
            }

            $errors = $community->errors();
            if (empty($errors) && $this->Communities->save($community)) {
                $this->Flash->success('Community presentation info updated');

                return $this->redirect([
                    'prefix' => 'admin',
                    'action' => 'index'
                ]);
            }
        }

        $optOutsTable = TableRegistry::get('OptOuts');
        $presentations = [
            ProductsTable::OFFICIALS_SURVEY => 'a',
            ProductsTable::OFFICIALS_SUMMIT => 'b',
            ProductsTable::ORGANIZATIONS_SURVEY => 'c',
            ProductsTable::ORGANIZATIONS_SUMMIT => 'd'
        ];
        foreach ($presentations as $productId => $letter) {
            $field = 'presentation_' . $letter . '_scheduled';
            $optedOut = $optOutsTable->find('all')
                ->where([
                    'community_id' => $communityId,
                    'product_id' => $productId
                ])
                ->count();
            $community->$field = $optedOut ? 'opted-out' : isset($community->{'presentation_' . $letter});
        }
        $productsTable = TableRegistry::get('Products');
        $this->set([
            'community' => $community,
            'titleForLayout' => $community->name . ' Presentations',
            'presentations' => $presentations,
            'products' => $productsTable->find('list')->toArray()
        ]);
    }

    /**
     * Method for /admin/communities/notes
     *
     * @param int $communityId Community ID
     * @return void
     */
    public function notes($communityId)
    {
        $community = $this->Communities->get($communityId);
        if ($this->request->is(['post', 'put'])) {
            $community = $this->Communities->patchEntity($community, $this->request->data());
            if ($community->errors()) {
                $this->Flash->error('There was an error updating this community\'s notes');
            } else {
                if ($this->Communities->save($community)) {
                    $this->Flash->success('Notes updated');
                }
            }
        }

        $this->set([
            'community' => $community,
            'titleForLayout' => $community->name . ' Notes'
        ]);
    }

    /**
     * Dispatches an event for Model.Community.afterScoreIncrease (or Decrease)
     *
     * @param int $previousScore Score before changing
     * @param int $newScore Score after changing
     * @param int $communityId Community ID
     * @return void
     */
    private function dispatchScoreChangeEvent($previousScore, $newScore, $communityId)
    {
        if ($previousScore == $newScore) {
            return;
        }
        $increase = $previousScore < $newScore;
        $eventName = 'Model.Community.afterScore' . ($increase ? 'Increase' : 'Decrease');
        $event = new Event($eventName, $this, ['meta' => compact('previousScore', 'newScore', 'communityId')]);
        $this->eventManager()->dispatch($event);
    }

    /**
     * Admin To-Do page
     *
     * @return void
     */
    public function toDo()
    {
        $communities = $this->Communities->find('all')
            ->select(['id', 'name'])
            ->where(['dummy' => false])
            ->order(['name' => 'ASC']);
        $AdminToDo = new AdminToDo();
        foreach ($communities as $community) {
            $community->toDo = $AdminToDo->getToDo($community->id);
        }

        $this->set([
            'communities' => $communities,
            'titleForLayout' => 'Admin To-Do'
        ]);
    }
}
