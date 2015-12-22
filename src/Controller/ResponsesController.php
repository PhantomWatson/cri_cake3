<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Responses Controller
 *
 * @property \App\Model\Table\ResponsesTable $Responses
 */
class ResponsesController extends AppController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Respondents', 'Surveys']
        ];
        $this->set('responses', $this->paginate($this->Responses));
        $this->set('_serialize', ['responses']);
    }

    /**
     * View method
     *
     * @param string|null $id Response id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $response = $this->Responses->get($id, [
            'contain' => ['Respondents', 'Surveys']
        ]);
        $this->set('response', $response);
        $this->set('_serialize', ['response']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $response = $this->Responses->newEntity();
        if ($this->request->is('post')) {
            $response = $this->Responses->patchEntity($response, $this->request->data);
            $errors = $response->errors();
            if (empty($errors) && $this->Responses->save($response)) {
                $this->Flash->success(__('The response has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The response could not be saved. Please, try again.'));
            }
        }
        $respondents = $this->Responses->Respondents->find('list', ['limit' => 200]);
        $surveys = $this->Responses->Surveys->find('list', ['limit' => 200]);
        $this->set(compact('response', 'respondents', 'surveys'));
        $this->set('_serialize', ['response']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Response id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $response = $this->Responses->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $response = $this->Responses->patchEntity($response, $this->request->data);
            $errors = $response->errors();
            if (empty($errors) && $this->Responses->save($response)) {
                $this->Flash->success(__('The response has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The response could not be saved. Please, try again.'));
            }
        }
        $respondents = $this->Responses->Respondents->find('list', ['limit' => 200]);
        $surveys = $this->Responses->Surveys->find('list', ['limit' => 200]);
        $this->set(compact('response', 'respondents', 'surveys'));
        $this->set('_serialize', ['response']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Response id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $response = $this->Responses->get($id);
        if ($this->Responses->delete($response)) {
            $this->Flash->success(__('The response has been deleted.'));
        } else {
            $this->Flash->error(__('The response could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
