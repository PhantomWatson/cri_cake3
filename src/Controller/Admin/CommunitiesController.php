<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Network\Exception\NotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;

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
        foreach ($this->filters as $group => $filter) {
            $this->Cookie->write("$cookieParentKey.filters.$group", $filter);
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
                    $this->paginate['conditions']['Community.score <'] = '5';
                    break;
                case 'completed':
                    $this->paginate['conditions']['Community.score'] = '5';
                    break;
                case 'fast_track':
                    $this->paginate['conditions']['Community.fast_track'] = true;
                    break;
                case 'normal_track':
                    $this->paginate['conditions']['Community.fast_track'] = false;
                    break;
                case 'all':
                default:
                    // No action
                    break;
            }
        }
    }

    private function adminIndexSetupPagination()
    {
        $this->paginate['contain'] = [
            'Client' => [
                'fields' => [
                    'Client.email',
                    'Client.name'
                ]
            ],
            'OfficialSurvey' => [
                'fields' => [
                    'OfficialSurvey.id',
                    'OfficialSurvey.sm_id',
                    'OfficialSurvey.alignment',
                    'OfficialSurvey.alignment_passed',
                    'OfficialSurvey.respondents_last_modified_date'
                ]
            ],
            'OrganizationSurvey' => [
                'fields' => [
                    'OrganizationSurvey.id',
                    'OrganizationSurvey.sm_id',
                    'OrganizationSurvey.alignment',
                    'OrganizationSurvey.alignment_passed',
                    'OrganizationSurvey.respondents_last_modified_date'
                ]
            ],
            'Area' => [
                'fields' => [
                    'Area.name'
                ]
            ]
        ];
        $this->paginate['group'] = 'Community.id';
        $this->paginate['fields'] = [
            'Community.id',
            'Community.name',
            'Community.fast_track',
            'Community.score',
            'Community.created'
        ];
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

            $links = array();
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
        $communityId = isset($this->request->data['Community']['id']) ?
            $this->request->data['Community']['id']
            : null;

        // Prevent one community from being linked to the survey of another community
        foreach (['official', 'organization'] as $type) {
            $model = ucwords($type).'Survey';
            $surveySmId = $this->request->data[$model]['sm_id'];
            $resultCommunityId = $surveysTable->getCommunityId($surveySmId);
            if ($surveySmId && $resultCommunityId && $resultCommunityId != $communityId) {
                $community = $this->Communities->get($communityId);
                $this->Flash->error('Error: The selected '.$type.'s survey is already assigned to '.$community->name);
                return false;
            }
        }

        $officialSmId = $this->request->data['OfficialSurvey']['sm_id'];
        $orgSmId = $this->request->data['OrganizationSurvey']['sm_id'];
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
        foreach (['OfficialSurvey', 'OrganizationSurvey'] as $type) {
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
        foreach (['OfficialSurvey', 'OrganizationSurvey'] as $type) {
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
        $model = ucwords($role);
        if (! isset($this->request->data["New$model"])) {
            return [];
        }

        $retval = [];
        $usersTable = TableRegistry::get('Users');
        foreach ($this->request->data["New$model"] as $newUser) {
            $user = $usersTable->newEntity($newUser);
            $user->role = $role;

            if ($user->errors()) {
                foreach ($user->errors() as $field => $error) {
                    $retval[] = $error;
                }
                continue;
            }

            if ($usersTable->save($user)) {
                $this->request->data[$model][] = $user->id;
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
        foreach ($this->request->data['clients'] as $clientId) {
            $associatedCommunityId = $this->Communities->getClientCommunityId($clientId);
            if ($associatedCommunityId && $associatedCommunityId != $communityId) {
                $community = $this->Communities->get($associatedCommunityId);
                $user = $usersTable->get($clientId);
                $retval[] = $user->name.' is already the client for '.$community->name;
            }
        }
        return $retval;
    }

    public function index()
    {
        if (isset($_GET['search'])) {
            $this->paginate['conditions']['Community.name LIKE'] = '%'.$_GET['search'].'%';
        } else {
            $this->adminIndexFilter();
        }
        $this->cookieSort('AdminCommunityIndex');
        $this->adminIndexSetupPagination();
        $this->adminIndexSetupFilterButtons();
        $this->set(array(
            'communities' => $this->paginate(),
            'title_for_layout' => 'Indiana Communities'
        ));
    }

    public function add()
    {
        if ($this->request->is('post')) {
            if (! $this->request->data['meeting_date_set']) {
                $this->request->data['town_meeting_date'] = null;
            }

            $community = $this->Communities->newEntity();
            $clientErrors = array_merge(
                $this->processNewAssociatedUsers('client'),
                $this->validateClients()
            );
            $consultantErrors = $this->processNewAssociatedUsers('consultant');
            if ($this->validateSelectedSurveys()) {
                if ($this->questionAndAnswerIdsAreSet()) {
                    $qnaSuccess = true;
                } else {
                    list($qnaSuccess, $qnaMsg) = $this->setSurveyQuestionAndAnswerIds();
                }
                $validates = $qnaSuccess
                    && $this->Communities->validates($this->request->data)
                    && empty($clientErrors)
                    && empty($consultantErrors);
                if ($validates && $this->Communities->saveAssociated($this->request->data)) {
                    $this->Flash->success('Community added');
                    $this->redirect([
                        'prefix' => 'admin',
                        'action' => 'index'
                    ]);
                } elseif (! $qnaSuccess) {
                    $this->Flash->error($qnaMsg);
                }
            }
            $this->set(compact('clientErrors', 'consultantErrors'));
        } else {
            $this->request->data['Community']['score'] = 0;
            $this->request->data['Community']['public'] = 0;
            $this->request->data['OfficialSurvey']['type'] = 'official';
            $this->request->data['OrganizationSurvey']['type'] = 'organization';
        }

        // Prepare selected clients for JS
        $this->loadModel('User');
        $clients = $this->User->getClientList();
        $selectedClients = [];
        if (isset($this->request->data['Client'])) {
            foreach ($this->request->data['Client'] as $clientId) {
                $selectedClients[] = [
                    'id' => $clientId,
                    'name' => $clients[$clientId]
                ];
            }
        }

        // Prepare selected consultants for JS
        $consultants = $this->User->getConsultantList();
        $selectedConsultants = [];
        if (isset($this->request->data['Consultant'])) {
            foreach ($this->request->data['Consultant'] as $consultantId) {
                $selectedConsultants[] = [
                    'id' => $consultantId,
                    'name' => $consultants[$consultantId]
                ];
            }
        }

        $usersTable = TableRegistry::get('Users');
        $surveysTable = TableRegistry::get('Surveys');
        $areasTable = TableRegistry::get('Areas');
        $this->set(array(
            'titleForLayout' => 'Add Community',
            'qnaIdFields' => $surveysTable->getQnaIdFieldNames(),
            'clients' => $usersTable->getClientList(),
            'consultants' => $usersTable->getConsultantList(),
            'selectedClients' => $selectedClients,
            'selectedConsultants' => $selectedConsultants,
            'areas' => $areasTable->find('list')
        ));
        $this->render('admin_form');
    }

    public function edit($communityId = null)
    {
        if (! $communityId) {
            throw new NotFoundException('Community ID not specified');
        }

        if (! $this->Communities->exists(['id' => $communityId])) {
            throw new NotFoundException('Community #'.$communityId.' not found');
        }

        if ($this->request->is('post') || $this->request->is('put')) {
            $this->request->data['id'] = $communityId;

            if (! $this->request->data['Community']['meeting_date_set']) {
                $this->request->data['Community']['town_meeting_date'] = null;
            }

            /* A workaround for deleting all of a community's clients/consultants.
             * A missing $this->request->data['Client'] key or an empty array isn't deleting existing clients as expected.
             * No clue why (Community->hasAndBelongsToMany->Client->unique is set to TRUE), but here's a hack. */
            if (! isset($this->request->data['Client']) || empty($this->request->data['Client'])) {
                $this->Communities->removeAllClientAssociations($communityId);
            }
            if (! isset($this->request->data['Consultant']) || empty($this->request->data['Consultant'])) {
                $this->Communities->removeAllConsultantAssociations($communityId);
            }

            $clientErrors = array_merge(
                $this->processNewAssociatedUsers('client'),
                $this->validateClients()
            );
            $consultantErrors = $this->processNewAssociatedUsers('consultant');
            if ($this->validateSelectedSurveys()) {
                if ($this->questionAndAnswerIdsAreSet()) {
                    $qnaSuccess = true;
                } else {
                    list($qnaSuccess, $qnaMsg) = $this->setSurveyQuestionAndAnswerIds();
                }
                $community = $this->Communities->newEntity($this->request->data);
                $validates = $qnaSuccess
                    && empty($community->errors())
                    && empty($clientErrors)
                    && empty($consultantErrors);
                if ($validates && $this->Communities->save($community)) {
                    $this->Flash->success('Community updated');
                    $this->redirect([
                        'prefix' => 'admin',
                        'action' => 'index'
                    ]);
                } elseif (! $qnaSuccess) {
                    $this->Flash->error($qnaMsg);
                }
            }
            $this->set(compact('clientErrors', 'consultantErrors'));
        } else {
            $this->request->data = $this->Communities->find('all')
                ->where(['id' => $communityId])
                ->contain([
                    'Client' => function ($q) {
                        return $q->select(['id', 'name']);
                    },
                    'Consultant' => function ($q) {
                        return $q->select(['id', 'name']);
                    },
                    'OfficialSurvey',
                    'OrganizationSurvey'
                ])
                ->first()
                ->toArray();
            $this->request->data['OfficialSurvey']['community_id'] = $communityId;
            $this->request->data['OfficialSurvey']['type'] = 'official';
            $this->request->data['OrganizationSurvey']['community_id'] = $communityId;
            $this->request->data['OrganizationSurvey']['type'] = 'organization';
        }

        // Prepare selected clients for JS
        $usersTable = TableRegistry::get('Users');
        $clients = $usersTable->getClientList();
        $selectedClients = [];
        if (isset($this->request->data['clients'])) {
            foreach ($this->request->data['clients'] as $client) {
                $clientId = isset($client['id']) ? $client['id'] : $client;
                $selectedClients[] = [
                    'id' => $clientId,
                    'name' => $clients[$clientId]
                ];
            }
        }

        // Prepare selected consultants for JS
        $consultants = $usersTable->getConsultantList();
        $selectedConsultants = array();
        if (isset($this->request->data['Consultant'])) {
            foreach ($this->request->data['Consultant'] as $consultant) {
                $consultantId = isset($consultant['id']) ? $consultant['id'] : $consultant;
                $selectedConsultants[] = [
                    'id' => $consultantId,
                    'name' => $consultants[$consultantId]
                ];
            }
        }

        $surveysTable = TableRegistry::get('Surveys');
        $areasTable = TableRegistry::get('Areas');
        $this->set(array(
            'communityId' => $communityId,
            'titleForLayout' => 'Edit '.$this->Communities->field('name'),
            'qnaIdFields' => $surveysTable->getQnaIdFieldNames(),
            'clients' => $usersTable->getClientList(),
            'consultants' => $usersTable->getConsultantList(),
            'selectedClients' => $selectedClients,
            'selectedConsultants' => $selectedConsultants,
            'areas' => $areasTable->find('list')
        ));
        $this->render('admin_form');
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
        $this->redirect($this->request->referer());
    }

    public function clients($communityId)
    {
        $community = $this->Communities->find('all')
            ->select(['id', 'name'])
            ->where(['id' => $communityId])
            ->contain([
                'Client' => function ($q) {
                    return $q
                        ->select(['name', 'email'])
                        ->order(['name' => 'ASC']);
                }
            ])
            ->first();
        if (! $result) {
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
        $returnedScore = $community->score;

        if ($this->request->is('post')) {
            $newScore = $this->request->data['score'];
            if ($newScore != $community->score) {
                $community->score = $newScore;
                if ($this->Communities->save($community)) {
                    $this->Flash->success('Community score '.($newScore > $community->score ? 'in' : 'de').'creased');
                    $returnedScore = $newScore;
                } else {
                    $this->Flash->error('There was an error updating this community');
                }
            }
        }

        $this->set([
            'titleForLayout' => $community->name.' Progress',
            'score' => $returnedScore,
            'criteria' => $this->Communities->getProgress($communityId, true),
            'fastTrack' => $community->fast_track
        ]);
    }

    public function spreadsheet()
    {
        // Set up filters and sorting
        if (isset($_GET['search'])) {
            $this->paginate['conditions']['Community.name LIKE'] = '%'.$_GET['search'].'%';
        } else {
            $this->adminIndexFilter();
        }
        $this->cookieSort('AdminCommunityIndex');
        $this->adminIndexSetupPagination();
        $this->adminIndexSetupFilterButtons();
        $this->paginate['limit'] = $this->Communities->find('all')->count();

        $communities = $this->paginate();

        $this->response->type(['excel2007' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        $this->response->type('excel2007');
        $this->response->download("CRI Overview.xlsx");
        $this->layout = 'spreadsheet';
        $this->set([
            'objPHPExcel' => $this->Communities->getSpreadsheetObject($communities)
        ]);
    }
}
