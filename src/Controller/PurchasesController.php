<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * Purchases Controller
 *
 * @property \App\Model\Table\PurchasesTable $Purchases
 */
class PurchasesController extends AppController
{

    /**
     * beforeFilter method
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeFilter(\Cake\Event\Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow('postback');
    }

    /**
     * This gets requested by the CashNet payment system following a successful purchase,
     * and logs a record of the purchase into the 'purchases' table.
     *
     * @return void
     */
    public function postback()
    {
        if ($this->request->data('respmessage') == 'SUCCESS') {
            $itemCode = explode('-', $this->request->data('itemcode1'));
            $productsTable = TableRegistry::get('Products');
            $productId = $productsTable->getIdFromItemCode($itemCode[1]);
            $purchase = $this->Purchases->newEntity([
                'user_id' => $this->request->data('custcode'),
                'community_id' => $this->request->data('ref1val1'),
                'product_id' => $productId,
                'source' => 'self',
                'postback' => base64_encode(serialize($this->request->data()))
            ]);
            if ($this->Purchases->save($purchase)) {

                // Dispatch event
                $product = $productsTable->get($productId);
                $event = new Event('Model.Product.afterPurchase', $this, ['meta' => [
                    'userId' => $this->request->data('custcode'),
                    'communityId' => $this->request->data('ref1val1'),
                    'productName' => $product->name
                ]]);
                $this->eventManager()->dispatch($event);
            }
        }

        $this->viewBuilder()->layout('blank');
    }
}
