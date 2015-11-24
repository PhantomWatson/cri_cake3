<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

/**
 * Purchases Controller
 *
 * @property \App\Model\Table\PurchasesTable $Purchases
 */
class PurchasesController extends AppController
{

    public function beforeFilter(\Cake\Event\Event $event)
    {
        parent::beforeFilter();
        $this->Auth->allow('postback');
    }

    /**
     * This gets requested by the CashNet payment system following a successful purchase,
     * and logs a record of the purchase into the 'purchases' table. */
    public function postback()
    {
        if ($this->request->data('respmessage') == 'SUCCESS') {
            $itemCode = explode('-', $this->request->data('itemcode1'));
            $productsTable = TableRegistry::get('Products');
            $purchase = $this->Purchases->newEntity([
                'user_id' => $this->request->data('custcode'),
                'community_id' => $this->request->data('ref1val1'),
                'product_id' => $productsTable->getIdFromItemCode($itemCode[1]),
                'postback' => base64_encode(serialize($this->request->data()))
            ]);
            $this->Purchases->save($purchase);
        }

        $this->viewBuilder()->layout('blank');
    }
}
