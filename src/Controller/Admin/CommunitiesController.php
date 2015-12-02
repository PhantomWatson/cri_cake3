<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Network\Exception\NotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\TableRegistry;

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

    private function validateSelectedSurveys()
    {
        $surveysTable = TableRegistry::get('Surveys');
        $communityId = isset($this->request->data['id']) ?
            $this->request->data['id']
            : null;

        // Prevent one community from being linked to the survey of another community
        foreach (['official', 'organization'] as $type) {
            $model = $type.'_survey';
            $surveySmId = $this->request->data[$model]['sm_id'];
            $resultCommunityId = $surveysTable->getCommunityId(['sm_id' => $surveySmId]);
            if ($surveySmId && $resultCommunityId && $resultCommunityId != $communityId) {
                $community = $this->Communities->get($communityId);
                $this->Flash->error('Error: The selected '.$type.'s survey is already assigned to '.$community->name);
                return false;
            }
        }

        $officialSmId = $this->request->data['official_survey']['sm_id'];
        $orgSmId = $this->request->data['organization_survey']['sm_id'];
        if ($officialSmId && $orgSmId && $officialSmId == $orgSmId) {
            $this->Flash->error("Error: You cannot select the same SurveyMonkey survey for both the officials survey <em>and</em> the organizations survey for this community.");
            return false;
        }

        return true;
    }

    /**
     * Queries the SurveyMonkey API to populate $this->request->data with the correct
     * values for the fields pwrrr_qid, production_aid, wholesale_aid, etc. to prepare
     * it for a call to saveAssociated()
     * @return array [success/error, error msg, data array]
     */
    private function setSurveyQuestionAndAnswerIds()
    {
        $surveysTable = TableRegistry::get('Surveys');
        $first = true;
        foreach (['official_survey', 'organization_survey'] as $type) {
            if (! $first) {
                // The SurveyMonkey API limits us to 2 API requests per second.
                // For extra safety, we'll delay for one second before the second API call.
                sleep(1);
            }

            if (! isset($this->request->data[$type]['sm_id']) || ! $this->request->data[$type]['sm_id']) {
                continue;
            }

            $smId = $this->request->data[$type]['sm_id'];
            $result = $surveysTable->getQuestionAndAnswerIds($smId);
            if ($result[0]) {
                $this->request->data[$type] = array_merge($this->request->data[$type], $result[2]);
            } else {
                return $result;
            }

            $first = false;
        }
        return $result;
    }

    /**
     * Returns true if Q&A IDs are set for any Community's associated survey (assuming Survey.sm_id is set)
     * @return boolean
     */
    private function questionAndAnswerIdsAreSet()
    {
        $surveysTable = TableRegistry::get('Surveys');
        $fieldnames = $surveysTable->getQnaIdFieldNames();
        foreach (['official_survey', 'organization_survey'] as $type) {
            if (! $this->request->data[$type]['sm_id']) {
                continue;
            }
            foreach ($fieldnames as $fieldname) {
                if (! isset($this->request->data[$type][$fieldname]) || ! $this->request->data[$type][$fieldname]) {
                    return false;
                }
            }
        }
        return true;
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
     * Passes $selectedClients and $selectedConsultants to the view to be used by Javascript
     * @param Entity $community
     * @param array $clients
     * @param array $consultants
     */
    private function prepareAssociatedUsersForJs($community, $clients, $consultants)
    {
        foreach (['clients', 'consultants'] as $role) {
            $selectedUsers = [];
            if (isset($community->$role)) {
                foreach ($community->$role as $user) {
                    $userId = isset($user['id']) ? $user['id'] : $user;
                    $selectedUsers[] = [
                        'id' => $userId,
                        'name' => ${$role}[$userId]
                    ];
                }
            }
            $this->set('selected'.ucwords($role), $selectedUsers);
        }
    }

    /**
     * Passes necessary variables the view to be used by the adding/editing form
     * @param Entity $community
     */
    private function prepareForm($community)
    {
        $usersTable = TableRegistry::get('Users');
        $clients = $usersTable->getClientList();
        $consultants = $usersTable->getConsultantList();
        $this->prepareAssociatedUsersForJs($community, $clients, $consultants);

        if ($this->request->action == 'add' && ! $this->request->is(['post', 'put'])) {
            $community->public = false;
            $community->score = 0;
        }

        $surveysTable = TableRegistry::get('Surveys');
        $areasTable = TableRegistry::get('Areas');
        $this->set([
            'areas' => $areasTable->find('list'),
            'clients' => $usersTable->getClientList(),
            'community' => $community,
            'consultants' => $usersTable->getConsultantList(),
            'qnaIdFields' => $surveysTable->getQnaIdFieldNames()
        ]);
    }

    private function validateForm($community)
    {
        $communityErrors = $community->errors();
        $validSurveySelection = $this->validateSelectedSurveys();
        $clientErrors = array_merge(
            $this->processNewAssociatedUsers('client'),
            $this->validateClients($community->id)
        );
        $consultantErrors = $this->processNewAssociatedUsers('consultant');
        $this->set(compact('clientErrors', 'consultantErrors'));
        return empty($communityErrors)
            && $validSurveySelection
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

            if ($this->questionAndAnswerIdsAreSet()) {
                $qnaSuccess = true;
            } else {
                list($qnaSuccess, $qnaMsg) = $this->setSurveyQuestionAndAnswerIds();
                if (! $qnaSuccess) {
                    $this->Flash->error($qnaMsg);
                }
            }

            $community = $this->Communities->patchEntity($community, $this->request->data());
            $validates = $this->validateForm($community);
            if ($validates && $qnaSuccess && $this->Communities->save($community)) {
                $this->Flash->success('Community added');
                return $this->redirect([
                    'prefix' => 'admin',
                    'action' => 'index'
                ]);
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
                'Clients',
                'Consultants',
                'OfficialSurvey',
                'OrganizationSurvey'
            ]
        ]);

        if ($this->request->is('post') || $this->request->is('put')) {
            $this->request->data['id'] = $communityId;

            if (! $this->request->data['meeting_date_set']) {
                $this->request->data['town_meeting_date'] = null;
            }

            /* A workaround for deleting all of a community's clients/consultants.
             * A missing $this->request->data['clients'] key or an empty array isn't deleting existing clients as expected.
             * No clue why (Community->hasAndBelongsToMany->Client->unique is set to TRUE), but here's a hack. */
            if (! isset($this->request->data['clients']) || empty($this->request->data['clients'])) {
                $this->Communities->removeAllClientAssociations($communityId);
            }
            if (! isset($this->request->data['consultants']) || empty($this->request->data['consultants'])) {
                $this->Communities->removeAllConsultantAssociations($communityId);
            }

            if ($this->questionAndAnswerIdsAreSet()) {
                $qnaSuccess = true;
            } else {
                list($qnaSuccess, $qnaMsg) = $this->setSurveyQuestionAndAnswerIds();
                if (! $qnaSuccess) {
                    $this->Flash->error($qnaMsg);
                }
            }

            $community = $this->Communities->patchEntity($community, $this->request->data(), [
                'associated' => ['Clients', 'Consultants']
            ]);
            $validates = $this->validateForm($community);
            if ($validates && $qnaSuccess && $this->Communities->save($community)) {
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
            'clients' => $community->clients
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
}
