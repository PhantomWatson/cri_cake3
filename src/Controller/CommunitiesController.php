<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Communities Controller
 *
 * @property \App\Model\Table\CommunitiesTable $Communities
 */
class CommunitiesController extends AppController
{

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
