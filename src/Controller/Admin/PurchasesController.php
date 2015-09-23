<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class PurchasesController extends AppController
{
    public $paginate = [
        'contain' => [
            'Community' => [
                'fields' => ['id', 'name']
            ],
            'Product' => [
                'fields' => ['id', 'description', 'price']
            ],
            'Refunder' => [
                'fields' => ['id', 'name']
            ],
            'User' => [
                'fields' => ['id', 'name', 'email', 'phone', 'title', 'organization']
            ]
        ],
        'fields' => ['id', 'created', 'refunded'],
        'limit' => 50,
        'order' => [
            'Purchase.created' => 'DESC'
        ]
    ];

    public function index()
    {
        $this->set([
            'titleForLayout' => 'Payment Records',
            'purchases' => $this->paginate()
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
                $this->Purchase->Refunder->id = $purchase->refunder_id;
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
}
