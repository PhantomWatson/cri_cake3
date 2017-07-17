<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\Purchase;
use Cake\Event\Event;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class PurchasesController extends AppController
{
    public $paginate = [
        'conditions' => ['Communities.dummy' => false],
        'contain' => [
            'Communities' => [
                'fields' => ['id', 'name', 'slug']
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
        'fields' => [
            'admin_added',
            'community_id',
            'created',
            'id',
            'notes',
            'product_id',
            'refunded',
            'refunder_id',
            'source',
            'user_id',
            'amount'
        ],
        'limit' => 50,
        'order' => [
            'Purchases.created' => 'DESC'
        ]
    ];

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->set([
            'titleForLayout' => 'Payment Records',
            'purchases' => $this->paginate()->toArray(),
            'sources' => $this->Purchases->getSourceOptions()
        ]);
    }

    /**
     * View method
     *
     * @param string $communitySlug Community slug
     * @return void
     * @throws NotFoundException
     */
    public function view($communitySlug)
    {
        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->find('slugged', ['slug' => $communitySlug])->first();

        if (! $community) {
            throw new NotFoundException('Community not found');
        }

        $this->paginate['conditions']['community_id'] = $community->id;
        $this->set([
            'communityId' => $community->id,
            'purchases' => $this
                ->paginate()
                ->toArray(),
            'sources' => $this->Purchases->getSourceOptions(),
            'titleForLayout' => $community->name . ' Payment Records'
        ]);
    }

    /**
     * Refund method
     *
     * @param int $purchaseId Purchase record ID
     * @return \Cake\Http\Response|null
     */
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

                    // Dispatch event
                    $productId = $purchase->product_id;
                    $productsTable = TableRegistry::get('Products');
                    $product = $productsTable->get($productId);
                    $event = new Event('Model.Purchase.afterRefund', $this, ['meta' => [
                        'communityId' => $purchase->community_id,
                        'productName' => $product->description
                    ]]);
                    $this->eventManager()->dispatch($event);
                } else {
                    $this->Flash->error('There was an error saving that refund record.');
                }
            }
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null
     */
    public function add()
    {
        $purchase = $this->Purchases->newEntity();
        $productsTable = TableRegistry::get('Products');
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['admin_added'] = true;
            $data['user_id'] = $this->Auth->user('id');
            $data['postback'] = '';
            $purchase = $this->Purchases->patchEntity($purchase, $data);
            $errors = $purchase->getErrors();
            if (empty($errors) && $this->Purchases->save($purchase)) {
                $this->Flash->success('Purchase record added');

                // Dispatch event
                $productId = $purchase->product_id;
                $product = $productsTable->get($productId);
                $event = new Event('Model.Purchase.afterAdminAdd', $this, ['meta' => [
                    'communityId' => $purchase->community_id,
                    'productName' => $product->description
                ]]);
                $this->eventManager()->dispatch($event);

                return $this->redirect([
                    'action' => 'index'
                ]);
            }
            $this->Flash->error('There was an error adding a new purchase record');
        }

        $communitiesTable = TableRegistry::get('Communities');
        $results = $productsTable->find('all')
            ->select(['id', 'description', 'price'])
            ->order(['id' => 'ASC']);
        $products = [];
        foreach ($results as $product) {
            $products[$product->id] = $product->description . ' ($' . number_format($product->price) . ')';
        }
        $this->set([
            'communities' => $communitiesTable->find('list')->order(['name' => 'ASC']),
            'products' => $products,
            'purchase' => $purchase,
            'titleForLayout' => 'Add a New Payment Record',
            'sources' => $this->Purchases->getSourceOptions()
        ]);
    }

    /**
     * Page for OCRA-funded purchases
     *
     * @return void
     */
    public function ocra()
    {
        $purchases = [
            'not yet billable' => [
                'purchases' => $this->Purchases
                    ->find('ocra')
                    ->find('notBillable')
                    ->toArray(),
                'form' => null,
                'date' => 'Purchased'
            ],
            'billable' => [
                'purchases' => $this->Purchases
                    ->find('ocra')
                    ->find('billable')
                    ->toArray(),
                'form' => [
                    'label' => 'Mark as Billed',
                    'action' => [
                        'prefix' => 'admin',
                        'controller' => 'Invoices',
                        'action' => 'markBilled'
                    ]
                ],
                'date' => 'Purchased'
            ],
            'billed' => [
                'purchases' => $this->Purchases
                    ->find('ocra')
                    ->find('billedUnpaid')
                    ->contain(['Invoices'])
                    ->toArray(),
                'form' => [
                    'label' => 'Mark as Paid',
                    'action' => [
                        'prefix' => 'admin',
                        'controller' => 'Invoices',
                        'action' => 'markPaid'
                    ]
                ],
                'date' => 'Billed'
            ],
            'paid' => [
                'purchases' => $this->Purchases
                    ->find('ocra')
                    ->find('paid')
                    ->toArray(),
                'form' => null,
                'date' => 'Billed'
            ]
        ];
        $totals = [];
        foreach ($purchases as $label => $group) {
            $costs = Hash::extract($group['purchases'], '{n}.product.price');
            $totals[$label] = array_sum($costs);
        }
        $this->set([
            'purchases' => $purchases,
            'titleForLayout' => 'Manage OCRA Funding',
            'totals' => $totals
        ]);
    }

    /**
     * Sets the 'amount' value for any purchase record with amount == 0
     *
     * Intended to be run once, immediately after the Purchases.amount field is added to the database
     *
     * @return void
     */
    public function populateEmptyAmounts()
    {
        $this->set('titleForLayout', 'Populate Empty Purchase Amounts');

        if (! $this->request->is('post')) {
            return;
        }

        $changedCount = 0;
        $purchases = $this->Purchases->find()
            ->where(['amount' => 0])
            ->contain(['Products']);

        foreach ($purchases as $purchase) {
            /** @var Purchase $purchase */
            $purchase = $this->Purchases->patchEntity($purchase, [
                'amount' => $purchase->product->price
            ]);

            if (! $this->Purchases->save($purchase)) {
                $msg = 'Error: <pre>' . print_r($purchase->getErrors(), true) . '</pre>';
                $this->Flash->error($msg);

                return;
            }

            $changedCount++;
        }

        if ($changedCount) {
            $msg = 'Updated ' . $changedCount . ' purchase ' . __n('record', 'records', $changedCount);
            $this->Flash->success($msg);

            return;
        }

        $this->Flash->set('No purchase records need updated');
    }
}
