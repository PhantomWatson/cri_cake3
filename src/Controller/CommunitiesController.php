<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Network\Exception\NotFoundException;

/**
 * Communities Controller
 *
 * @property \App\Model\Table\CommunitiesTable $Communities
 */
class CommunitiesController extends AppController
{

    public function beforeRender(Event $event)
    {
        $this->getView()->loadHelper('GoogleCharts.GoogleCharts');
    }

    public function isAuthorized($user)
    {
        if ($this->action == 'view') {
            if (isset($this->request->pass[0]) && ! empty($this->request->pass[0])) {
                $communityId = $this->request->pass[0];
            } elseif (isset($_GET['cid']) && ! empty($_GET['cid'])) {
                $communityId = $_GET['cid'];
            } else {
                throw new NotFoundException('Community ID not specified');
            }
            $userId = isset($user['id']) ? $user['id'] : null;
            $usersTable = TableRegistry::get('Users');
            return $usersTable->canAccessCommunity($userId, $communityId);
        }

        return parent::isAuthorized($user);
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
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Areas']
        ];
        $this->set('communities', $this->paginate($this->Communities));
        $this->set('_serialize', ['communities']);
    }

    /**
     * View method
     *
     * @param string|null $id Community id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $community = $this->Communities->get($id, [
            'contain' => ['Areas', 'Purchases', 'Surveys', 'SurveysBackup']
        ]);
        $this->set('community', $community);
        $this->set('_serialize', ['community']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $community = $this->Communities->newEntity();
        if ($this->request->is('post')) {
            $community = $this->Communities->patchEntity($community, $this->request->data);
            if ($this->Communities->save($community)) {
                $this->Flash->success(__('The community has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The community could not be saved. Please, try again.'));
            }
        }
        $areas = $this->Communities->Areas->find('list', ['limit' => 200]);
        $this->set(compact('community', 'areas'));
        $this->set('_serialize', ['community']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Community id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $community = $this->Communities->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $community = $this->Communities->patchEntity($community, $this->request->data);
            if ($this->Communities->save($community)) {
                $this->Flash->success(__('The community has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The community could not be saved. Please, try again.'));
            }
        }
        $areas = $this->Communities->Areas->find('list', ['limit' => 200]);
        $this->set(compact('community', 'areas'));
        $this->set('_serialize', ['community']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Community id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $community = $this->Communities->get($id);
        if ($this->Communities->delete($community)) {
            $this->Flash->success(__('The community has been deleted.'));
        } else {
            $this->Flash->error(__('The community could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
