<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Statistics Controller
 *
 * @property \App\Model\Table\StatisticsTable $Statistics
 */
class StatisticsController extends AppController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Areas', 'StatCategories']
        ];
        $this->set('statistics', $this->paginate($this->Statistics));
        $this->set('_serialize', ['statistics']);
    }

    /**
     * View method
     *
     * @param string|null $id Statistic id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $statistic = $this->Statistics->get($id, [
            'contain' => ['Areas', 'StatCategories']
        ]);
        $this->set('statistic', $statistic);
        $this->set('_serialize', ['statistic']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $statistic = $this->Statistics->newEntity();
        if ($this->request->is('post')) {
            $statistic = $this->Statistics->patchEntity($statistic, $this->request->data);
            $errors = $statistic->errors();
            if (empty($errors) && $this->Statistics->save($statistic)) {
                $this->Flash->success(__('The statistic has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The statistic could not be saved. Please, try again.'));
            }
        }
        $areas = $this->Statistics->Areas->find('list', ['limit' => 200]);
        $statCategories = $this->Statistics->StatCategories->find('list', ['limit' => 200]);
        $this->set(compact('statistic', 'areas', 'statCategories'));
        $this->set('_serialize', ['statistic']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Statistic id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $statistic = $this->Statistics->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $statistic = $this->Statistics->patchEntity($statistic, $this->request->data);
            $errors = $statistic->errors();
            if (empty($errors) && $this->Statistics->save($statistic)) {
                $this->Flash->success(__('The statistic has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The statistic could not be saved. Please, try again.'));
            }
        }
        $areas = $this->Statistics->Areas->find('list', ['limit' => 200]);
        $statCategories = $this->Statistics->StatCategories->find('list', ['limit' => 200]);
        $this->set(compact('statistic', 'areas', 'statCategories'));
        $this->set('_serialize', ['statistic']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Statistic id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $statistic = $this->Statistics->get($id);
        if ($this->Statistics->delete($statistic)) {
            $this->Flash->success(__('The statistic has been deleted.'));
        } else {
            $this->Flash->error(__('The statistic could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
