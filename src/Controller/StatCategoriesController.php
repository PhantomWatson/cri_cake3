<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * StatCategories Controller
 *
 * @property \App\Model\Table\StatCategoriesTable $StatCategories
 */
class StatCategoriesController extends AppController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->set('statCategories', $this->paginate($this->StatCategories));
        $this->set('_serialize', ['statCategories']);
    }

    /**
     * View method
     *
     * @param string|null $id Stat Category id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $statCategory = $this->StatCategories->get($id, [
            'contain' => ['Statistic']
        ]);
        $this->set('statCategory', $statCategory);
        $this->set('_serialize', ['statCategory']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $statCategory = $this->StatCategories->newEntity();
        if ($this->request->is('post')) {
            $statCategory = $this->StatCategories->patchEntity($statCategory, $this->request->data);
            if ($this->StatCategories->save($statCategory)) {
                $this->Flash->success(__('The stat category has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The stat category could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('statCategory'));
        $this->set('_serialize', ['statCategory']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Stat Category id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $statCategory = $this->StatCategories->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $statCategory = $this->StatCategories->patchEntity($statCategory, $this->request->data);
            if ($this->StatCategories->save($statCategory)) {
                $this->Flash->success(__('The stat category has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The stat category could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('statCategory'));
        $this->set('_serialize', ['statCategory']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Stat Category id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $statCategory = $this->StatCategories->get($id);
        if ($this->StatCategories->delete($statCategory)) {
            $this->Flash->success(__('The stat category has been deleted.'));
        } else {
            $this->Flash->error(__('The stat category could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
