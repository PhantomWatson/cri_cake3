<?php
namespace App\Controller\Admin;

use App\AdminToDo\AdminToDo;
use App\Controller\AppController;
use App\Model\Entity\Community;
use App\Model\Table\AreasTable;
use App\Model\Table\OptOutsTable;
use App\Model\Table\ProductsTable;
use App\Model\Table\PurchasesTable;
use App\Model\Table\SettingsTable;
use App\Model\Table\SurveysTable;
use App\Model\Table\UsersTable;
use Cake\Database\Expression\QueryExpression;
use Cake\Event\Event;
use Cake\Mailer\MailerAwareTrait;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Hash;

class CommunitiesController extends AppController
{
    use MailerAwareTrait;

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
        /** @var AreasTable $areasTable */
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
        $communities = $this->Communities->find()
            ->contain([
                'Clients',
                'OfficialSurvey',
                'OptOuts',
                'OrganizationSurvey',
                'ParentAreas'
            ])
            ->order(['Communities.name' => 'ASC'])
            ->toArray();
        $communities = $this->addSurveyStatuses($communities);

        $this->set([
            'communities' => $communities,
            'perPage' => 20,
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
            /** @var $community Community */
            $community = $this->Communities->patchEntity($community, $this->request->getData(), [
                'associated' => ['OfficialSurvey', 'OrganizationSurvey']
            ]);

            $errors = $community->getErrors();
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
                    $community->slug,
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
            $community->score = 1;
            /** @var $settingsTable SettingsTable */
            $settingsTable = TableRegistry::get('Settings');
            $community->intAlignmentAdjustment = $settingsTable->getIntAlignmentAdjustment();
            $community->intAlignmentThreshold = $settingsTable->getIntAlignmentThreshold();
        }

        $this->prepareForm($community);
        $this->set('titleForLayout', 'Add Community');
        $this->render('form');

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $communitySlug Community slug
     * @return \Cake\Http\Response|null
     * @throws NotFoundException
     */
    public function edit($communitySlug = null)
    {
        if (! $communitySlug) {
            throw new NotFoundException('Community not specified');
        }

        $community = $this->Communities->find('slugged', ['slug' => $communitySlug])
            ->contain([
                'OfficialSurvey',
                'OrganizationSurvey'
            ])
            ->first();
        if (!$community) {
            throw new NotFoundException('Community not found');
        }

        $previousScore = $community->score;

        if ($this->request->is('post') || $this->request->is('put')) {
            /** @var $community Community */
            $community = $this->Communities->patchEntity($community, $this->request->getData(), [
                'associated' => ['OfficialSurvey', 'OrganizationSurvey']
            ]);
            $areaUpdated = $community->isDirty('local_area_id') || $community->isDirty('parent_area_id');
            $errors = $community->getErrors();

            if (empty($errors) && $this->Communities->save($community)) {
                $this->Flash->success('Community updated');

                // Dispatch events
                if ($previousScore != $community->score) {
                    $this->dispatchScoreChangeEvent($previousScore, $community->score, $community->id);
                }
                if ($areaUpdated) {
                    $event = new Event('Model.Community.afterUpdateCommunityArea', $this, ['meta' => [
                        'communityId' => $community->id
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
            'communityId' => $community->id,
            'titleForLayout' => 'Edit ' . $community->name
        ]);
        $this->render('form');

        return null;
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
     * @param string $communitySlug Community slug
     * @return void
     */
    public function clients($communitySlug)
    {
        $community = $this->Communities->find('all')
            ->select(['id', 'name'])
            ->where(['slug' => $communitySlug])
            ->contain([
                'Clients' => function ($q) {
                    /** @var $q Query */

                    return $q->order(['name' => 'ASC']);
                },
                'Surveys' => function ($q) {
                    /** @var $q Query */

                    return $q->where(['type' => 'official']);
                }
            ])
            ->first();
        if (! $community) {
            throw new NotFoundException('Community not found');
        }
        $this->set([
            'community' => $community,
            'titleForLayout' => $community->name . ' Clients'
        ]);
    }

    /**
     * Progress method
     *
     * @param string $communitySlug Community slug
     * @return void
     * @throws NotFoundException
     */
    public function progress($communitySlug)
    {
        $community = $this->Communities->find('slugged', ['slug' => $communitySlug])->first();
        if (!$community) {
            throw new NotFoundException('Community not found');
        }

        $previousScore = $community->score;

        if ($this->request->is('put')) {
            $options = ['fieldList' => ['score']];

            /** @var $community Community */
            $community = $this->Communities->patchEntity($community, $this->request->getData(), $options);
            if ($community->isDirty('score')) {
                if ($this->Communities->save($community)) {
                    $verbed = $community->score > $previousScore ? 'increased' : 'decreased';
                    $this->Flash->success('Community score ' . $verbed);
                    $this->dispatchScoreChangeEvent($previousScore, $community->score, $community->id);
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
            'criteria' => $this->Communities->getProgress($community->id, true)
        ]);
    }

    /**
     * Client home method
     *
     * @param string $communitySlug Community slug
     * @return \Cake\Http\Response|null
     * @throws NotFoundException
     */
    public function clienthome($communitySlug)
    {
        $community = $this->Communities->find('slugged', ['slug' => $communitySlug])
            ->select(['id'])
            ->first();

        if (!$community) {
            throw new NotFoundException('Community not found');
        }

        $this->Cookie->write('communityId', $community->id);
        $this->loadComponent('ClientHome');
        $prepResult = $this->ClientHome->prepareClientHome($community->id);
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

        /** @var $usersTable UsersTable */
        $usersTable = TableRegistry::get('Users');

        if ($this->request->is('post')) {
            $client = $usersTable->newEntity($this->request->getData());
            $client->role = 'client';
            $community = $this->Communities->get($communityId);
            $client->client_communities = [$community];
            $client->password = $this->request->getData('unhashed_password');
            $errors = $client->getErrors();
            if (empty($errors) && $usersTable->save($client)) {
                try {
                    $this->getMailer('User')->send('newAccount', [
                        $client,
                        $this->request->getData('unhashed_password')
                    ]);

                    $msg = 'Client account created for ' . $client->name . ' and login instructions emailed';
                    $this->Flash->success($msg);

                    $event = new Event('Model.Community.afterAddClient', $this, ['meta' => [
                        'communityId' => $communityId,
                        'clientName' => $client->name,
                        'clientEmail' => $client->email
                    ]]);
                    $this->eventManager()->dispatch($event);

                    return $this->redirect([
                        'action' => 'clients',
                        $community->slug
                    ]);
                } catch (\Exception $e) {
                    $msg = 'There was an error emailing account login info to ' . $client->name . '.';
                    $msg .= ' No new account was created. Please contact an administrator for assistance.';
                    $this->Flash->error($msg);
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

        return null;
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
        /** @var $usersTable UsersTable */
        $usersTable = TableRegistry::get('Users');
        $community = $this->Communities->get($communityId);

        $this->set([
            'clients' => $usersTable->getClientList(),
            'communityId' => $communityId,
            'communityName' => $community->name,
            'titleForLayout' => 'Add a New Client for ' . $community->name
        ]);

        if (! $this->request->is('post')) {
            $this->set('client', $usersTable->newEntity());

            return null;
        }

        $clientId = $this->request->getData('client_id');
        $client = $usersTable->get($clientId, ['contain' => ['ClientCommunities']]);
        $alreadyLinked = false;

        // Unlink client from any other community
        if (! empty($client['client_communities'])) {
            foreach ($client['client_communities'] as $linkedCommunity) {
                if ($linkedCommunity['id'] == $communityId) {
                    $alreadyLinked = true;
                    continue;
                }
                $linkedCommunityEntity = $this->Communities->get($linkedCommunity['id']);
                $usersTable->ClientCommunities->unlink($client, [$linkedCommunityEntity]);
                $msg = $client->name . '\'s association with ' . $linkedCommunity['name'] . ' has been removed';
                $this->Flash->notification($msg);
            }
        }

        // Client is already linked
        if ($alreadyLinked) {
            $this->Flash->notification($client->name . ' is already assigned to ' . $community->name);

            return $this->redirect([
                'action' => 'clients',
                $community->slug
            ]);
        }

        // Link client with this community
        if ($this->Communities->Clients->link($community, [$client])) {
            $this->Flash->success($client->name . ' is now assigned to ' . $community->name);

            $event = new Event('Model.Community.afterAddClient', $this, ['meta' => [
                'communityId' => $communityId,
                'clientName' => $client->name,
                'clientEmail' => $client->email
            ]]);
            $this->eventManager()->dispatch($event);

            return $this->redirect([
                'action' => 'clients',
                $community->slug
            ]);
        }

        $this->Flash->error('There was an error assigning ' . $client->name . ' to ' . $community->name);

        return null;
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
            ->where(function ($exp) {
                /** @var $exp QueryExpression */

                return $exp->in('name', ['intAlignmentAdjustment', 'intAlignmentThreshold']);
            })
            ->toArray();
        $settings = Hash::combine($settings, '{n}.name', '{n}.value');
        $conditions = [];
        $includeDummy = (bool)$this->request->getQuery('show-dummy');
        if (! $includeDummy) {
            $conditions['dummy'] = 0;
        }
        $communities = $this->Communities->find('all')
            ->select([
                'id',
                'name',
                'intAlignmentAdjustment',
                'intAlignmentThreshold',
                'slug'
            ])
            ->where($conditions)
            ->order(['created' => 'DESC']);

        /** @var $surveysTable SurveysTable */
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
     * @param null|string $communitySlug Community slug
     * @return \Cake\Http\Response|null
     * @throws NotFoundException
     */
    public function presentations($communitySlug = null)
    {
        if (! $communitySlug) {
            throw new NotFoundException('Community not specified');
        }

        /** @var $community Community */
        $community = $this->Communities->find('slugged', ['slug' => $communitySlug])->first();
        if (! $community) {
            throw new NotFoundException('Community not found');
        }

        $community = $this->Communities->patchEntity($community, $this->request->getData());
        /** @var $optOutsTable OptOutsTable */
        $optOutsTable = TableRegistry::get('OptOuts');
        if ($this->request->is('post') || $this->request->is('put')) {
            foreach (['a', 'b', 'c', 'd'] as $letter) {
                $selection = $this->request->getData('presentation_' . $letter . '_scheduled');
                if ($selection == 'opted-out') {
                    $addResult = $optOutsTable->addOptOut([
                        'user_id' => $this->Auth->user('id'),
                        'community_id' => $community->id,
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

            $errors = $community->getErrors();
            if (empty($errors) && $this->Communities->save($community)) {
                $this->Flash->success('Community presentation info updated');

                return $this->redirect([
                    'prefix' => 'admin',
                    'action' => 'index'
                ]);
            }
        }

        // Populate $community->presentation_[a, b, c, d]_scheduled with true, false, or 'opted-out'
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
                    'community_id' => $community->id,
                    'product_id' => $productId
                ])
                ->count();
            $community->$field = $optedOut ? 'opted-out' : isset($community->{'presentation_' . $letter});
        }

        /** @var $purchasesTable PurchasesTable */
        $purchasesTable = TableRegistry::get('Purchases');
        $purchases = $purchasesTable->getAllForCommunity($community->id);
        $purchasedProductIds = Hash::extract($purchases, '{n}.product_id');

        $productsTable = TableRegistry::get('Products');
        $this->set([
            'community' => $community,
            'titleForLayout' => $community->name . ' Presentations',
            'presentations' => $presentations,
            'products' => $productsTable->find('list')->toArray(),
            'purchasedProductIds' => $purchasedProductIds
        ]);

        return null;
    }

    /**
     * Method for /admin/communities/notes
     *
     * @param string $communitySlug Community slug
     * @return void
     * @throws NotFoundException
     */
    public function notes($communitySlug)
    {
        /** @var $community Community */
        $community = $this->Communities->find('slugged', ['slug' => $communitySlug])->first();

        if (! $community) {
            throw new NotFoundException('Community not found');
        }

        if ($this->request->is(['post', 'put'])) {
            $community = $this->Communities->patchEntity($community, $this->request->getData());
            if ($community->getErrors()) {
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
        $partyFilter = $this->request->getQuery('responsible');
        $AdminToDo = new AdminToDo();
        $communities = $this->Communities->find('all')
            ->select(['id', 'name'])
            ->where(['dummy' => false])
            ->order(['name' => 'ASC'])
            ->toArray();
        foreach ($communities as $key => $community) {
            $community['toDo'] = $AdminToDo->getToDo($community['id']);

            // Exclude any communities that are done participating in CRI
            if (isset($community['toDo']['done'])) {
                unset($communities[$key]);
                continue;
            }

            // Apply filters
            if (! $partyFilter) {
                continue;
            }
            if (! $community['toDo']['responsible']) {
                unset($communities[$key]);
                continue;
            }
            $matchesFilter = false;
            foreach ($community['toDo']['responsible'] as $party) {
                if (stripos($party, $partyFilter) !== false) {
                    $matchesFilter = true;
                }
            }
            if (! $matchesFilter) {
                unset($communities[$key]);
            }
        }

        $title = 'Admin To-Do';
        if ($partyFilter) {
            $title .= ": $partyFilter Tasks";
        }

        $this->set([
            'communities' => $communities,
            'titleForLayout' => $title
        ]);
    }

    /**
     * Method for /admin/communities/activate
     *
     * @param string $communitySlug Community slug
     * @return void
     * @throws NotFoundException
     */
    public function activate($communitySlug)
    {
        /** @var $community Community */
        $community = $this->Communities->find('slugged', ['slug' => $communitySlug])->first();

        if (! $community) {
            throw new NotFoundException('Community not found');
        }

        $currentlyActive = $community->active;
        if ($this->request->is('put')) {
            $community = $this->Communities->patchEntity($community, $this->request->getData());
            if ($community->getErrors()) {
                $msg = 'There was an error updating the selected community';
                $this->Flash->error($msg);
            } elseif ($this->Communities->save($community)) {
                $currentlyActive = $this->request->getData('active');
                $msg = 'Community ' . ($currentlyActive ? 'reactivated' : 'marked inactive');
                $this->Flash->success($msg);

                // Event
                $eventName = 'Model.Community.after' . ($currentlyActive ? 'Activate' : 'Deactivate');
                $event = new Event($eventName, $this, ['meta' => [
                    'communityId' => $community->id
                ]]);
                $this->eventManager()->dispatch($event);
            } else {
                $msg = 'There was an error updating the selected community';
                $this->Flash->error($msg);
            }
        }

        if ($currentlyActive) {
            $title = 'Mark ' . $community->name . ' inactive';
        } else {
            $title = 'Reactivate ' . $community->name;
        }

        $this->set([
            'community' => $community,
            'currentlyActive' => $currentlyActive,
            'parties' => [
                'All',
                'ICI',
                'CBER',
                'Client'
            ],
            'titleForLayout' => $title,
            'warning' => $this->Communities->getDeactivationWarning($community->id)
        ]);
    }
}
