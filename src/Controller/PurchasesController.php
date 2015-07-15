<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Purchases Controller
 *
 * @property \App\Model\Table\PurchasesTable $Purchases
 */
class PurchasesController extends AppController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Users', 'Communities', 'Products', 'Refunders']
        ];
        $this->set('purchases', $this->paginate($this->Purchases));
        $this->set('_serialize', ['purchases']);
    }

    /**
     * View method
     *
     * @param string|null $id Purchase id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $purchase = $this->Purchases->get($id, [
            'contain' => ['Users', 'Communities', 'Products', 'Refunders']
        ]);
        $this->set('purchase', $purchase);
        $this->set('_serialize', ['purchase']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $purchase = $this->Purchases->newEntity();
        if ($this->request->is('post')) {
            $purchase = $this->Purchases->patchEntity($purchase, $this->request->data);
            if ($this->Purchases->save($purchase)) {
                $this->Flash->success(__('The purchase has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The purchase could not be saved. Please, try again.'));
            }
        }
        $users = $this->Purchases->Users->find('list', ['limit' => 200]);
        $communities = $this->Purchases->Communities->find('list', ['limit' => 200]);
        $products = $this->Purchases->Products->find('list', ['limit' => 200]);
        $refunders = $this->Purchases->Refunders->find('list', ['limit' => 200]);
        $this->set(compact('purchase', 'users', 'communities', 'products', 'refunders'));
        $this->set('_serialize', ['purchase']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Purchase id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $purchase = $this->Purchases->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $purchase = $this->Purchases->patchEntity($purchase, $this->request->data);
            if ($this->Purchases->save($purchase)) {
                $this->Flash->success(__('The purchase has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The purchase could not be saved. Please, try again.'));
            }
        }
        $users = $this->Purchases->Users->find('list', ['limit' => 200]);
        $communities = $this->Purchases->Communities->find('list', ['limit' => 200]);
        $products = $this->Purchases->Products->find('list', ['limit' => 200]);
        $refunders = $this->Purchases->Refunders->find('list', ['limit' => 200]);
        $this->set(compact('purchase', 'users', 'communities', 'products', 'refunders'));
        $this->set('_serialize', ['purchase']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Purchase id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $purchase = $this->Purchases->get($id);
        if ($this->Purchases->delete($purchase)) {
            $this->Flash->success(__('The purchase has been deleted.'));
        } else {
            $this->Flash->error(__('The purchase could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
