<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Respondents Controller
 *
 * @property \App\Model\Table\RespondentsTable $Respondents
 */
class RespondentsController extends AppController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Surveys', 'SmRespondents']
        ];
        $this->set('respondents', $this->paginate($this->Respondents));
        $this->set('_serialize', ['respondents']);
    }

    /**
     * View method
     *
     * @param string|null $id Respondent id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $respondent = $this->Respondents->get($id, [
            'contain' => ['Surveys', 'SmRespondents', 'Responses']
        ]);
        $this->set('respondent', $respondent);
        $this->set('_serialize', ['respondent']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $respondent = $this->Respondents->newEntity();
        if ($this->request->is('post')) {
            $respondent = $this->Respondents->patchEntity($respondent, $this->request->data);
            if ($this->Respondents->save($respondent)) {
                $this->Flash->success(__('The respondent has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The respondent could not be saved. Please, try again.'));
            }
        }
        $surveys = $this->Respondents->Surveys->find('list', ['limit' => 200]);
        $smRespondents = $this->Respondents->SmRespondents->find('list', ['limit' => 200]);
        $this->set(compact('respondent', 'surveys', 'smRespondents'));
        $this->set('_serialize', ['respondent']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Respondent id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $respondent = $this->Respondents->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $respondent = $this->Respondents->patchEntity($respondent, $this->request->data);
            if ($this->Respondents->save($respondent)) {
                $this->Flash->success(__('The respondent has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The respondent could not be saved. Please, try again.'));
            }
        }
        $surveys = $this->Respondents->Surveys->find('list', ['limit' => 200]);
        $smRespondents = $this->Respondents->SmRespondents->find('list', ['limit' => 200]);
        $this->set(compact('respondent', 'surveys', 'smRespondents'));
        $this->set('_serialize', ['respondent']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Respondent id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $respondent = $this->Respondents->get($id);
        if ($this->Respondents->delete($respondent)) {
            $this->Flash->success(__('The respondent has been deleted.'));
        } else {
            $this->Flash->error(__('The respondent could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
