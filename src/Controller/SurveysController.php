<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Surveys Controller
 *
 * @property \App\Model\Table\SurveysTable $Surveys
 */
class SurveysController extends AppController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Communities', 'Sms']
        ];
        $this->set('surveys', $this->paginate($this->Surveys));
        $this->set('_serialize', ['surveys']);
    }

    /**
     * View method
     *
     * @param string|null $id Survey id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $survey = $this->Surveys->get($id, [
            'contain' => ['Communities', 'Sms', 'Respondents', 'Responses']
        ]);
        $this->set('survey', $survey);
        $this->set('_serialize', ['survey']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $survey = $this->Surveys->newEntity();
        if ($this->request->is('post')) {
            $survey = $this->Surveys->patchEntity($survey, $this->request->data);
            if ($this->Surveys->save($survey)) {
                $this->Flash->success(__('The survey has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The survey could not be saved. Please, try again.'));
            }
        }
        $communities = $this->Surveys->Communities->find('list', ['limit' => 200]);
        $sms = $this->Surveys->Sms->find('list', ['limit' => 200]);
        $this->set(compact('survey', 'communities', 'sms'));
        $this->set('_serialize', ['survey']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Survey id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $survey = $this->Surveys->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $survey = $this->Surveys->patchEntity($survey, $this->request->data);
            if ($this->Surveys->save($survey)) {
                $this->Flash->success(__('The survey has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The survey could not be saved. Please, try again.'));
            }
        }
        $communities = $this->Surveys->Communities->find('list', ['limit' => 200]);
        $sms = $this->Surveys->Sms->find('list', ['limit' => 200]);
        $this->set(compact('survey', 'communities', 'sms'));
        $this->set('_serialize', ['survey']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Survey id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $survey = $this->Surveys->get($id);
        if ($this->Surveys->delete($survey)) {
            $this->Flash->success(__('The survey has been deleted.'));
        } else {
            $this->Flash->error(__('The survey could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
