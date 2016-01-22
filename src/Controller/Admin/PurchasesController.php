<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class PurchasesController extends AppController
{
    public $paginate = [
        'contain' => [
            'Communities' => [
                'fields' => ['id', 'name']
            ],
            'Products' => [
                'fields' => ['id', 'description', 'price']
            ],
            'Refunders' => [
                'fields' => ['id', 'name']
            ],
            'Users' => [
                'fields' => ['id', 'name', 'email', 'phone', 'title', 'organization']
            ]
        ],
        'fields' => ['id', 'created', 'refunded', 'notes', 'admin_added'],
        'limit' => 50,
        'order' => [
            'Purchases.created' => 'DESC'
        ]
    ];

    public function index()
    {
        $this->set([
            'titleForLayout' => 'Payment Records',
            'purchases' => $this->paginate()->toArray()
        ]);
    }

    public function refund($purchaseId)
    {
        try {
            $purchase = $this->Purchases->get($purchaseId);
        } catch (RecordNotFoundException $e) {
            $this->Flash->error("Payment record #$purchaseId not found.");
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is('post')) {
            // Bounce user back if the purchase was already refunded
            if ($purchase->refunded) {
                $timestamp = strtotime($purchase->refunded);
                $date = date('F j, Y', $timestamp);
                $usersTable = TableRegistry::get('Users');
                try {
                    $user = $usersTable->get($purchase->refunder_id);
                    $admin = $user->name;
                } catch (RecordNotFoundException $e) {
                    $admin = "(unknown user #$purchase->refunder_id)";
                }
                $this->Flash->error("That purchase record was already marked refunded on $date by $admin.");
            } else {
                // Record refund
                $purchase->refunded = date('Y-m-d H:i:s');
                $purchase->refunder_id = $this->Auth->user('id');
                if ($this->Purchases->save($purchase)) {
                    $this->Flash->success('Refund recorded.');
                } else {
                    $this->Flash->error('There was an error saving that refund record.');
                }
            }
        }

        return $this->redirect(['action' => 'index']);
    }

    public function add()
    {
        $purchase = $this->Purchases->newEntity();
        if ($this->request->is('post')) {
            $this->request->data['admin_added'] = true;
            $this->request->data['user_id'] = $this->Auth->user('id');
            $this->request->data['postback'] = '';
            $purchase = $this->Purchases->patchEntity($purchase, $this->request->data());
            $errors = $purchase->errors();
            if (empty($errors) && $this->Purchases->save($purchase)) {
                $this->Flash->success('Purchase record added');
                return $this->redirect([
                    'action' => 'index'
                ]);
            }
            $this->Flash->error('There was an error adding a new purchase record');
        }

        $communitiesTable = TableRegistry::get('Communities');
        $productsTable = TableRegistry::get('Products');
        $results = $productsTable->find('all')
            ->select(['id', 'description', 'price'])
            ->order(['id' => 'ASC']);
        $products = [];
        foreach ($results as $product) {
            $products[$product->id] = $product->description.' ($'.number_format($product->price).')';
        }
        $this->set([
            'communities' => $communitiesTable->find('list')->order(['name' => 'ASC']),
            'products' => $products,
            'purchase' => $purchase,
            'titleForLayout' => 'Add a New Payment Record'
        ]);
    }
}
