<?php
namespace App\Controller;

use App\Model\Table\ProductsTable;
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
    public function beforeFilter(Event $event)
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
        if ($this->request->getData('respmessage') == 'SUCCESS') {
            /** @var ProductsTable $productsTable */
            $productsTable = TableRegistry::get('Products');
            $itemCode = explode('-', $this->request->getData('itemcode1'));
            $productId = $productsTable->getIdFromItemCode($itemCode[1]);
            $amount = (float)$this->request->getData('amount1') * 100; // Stored as cents
            $purchase = $this->Purchases->newEntity([
                'user_id' => $this->request->getData('custcode'),
                'community_id' => $this->request->getData('ref1val1'),
                'product_id' => $productId,
                'amount' => $amount,
                'source' => 'self',
                'postback' => base64_encode(serialize($this->request->getData()))
            ]);
            if ($this->Purchases->save($purchase)) {
                // Dispatch event
                $product = $productsTable->get($productId);
                $event = new Event('Model.Product.afterPurchase', $this, ['meta' => [
                    'userId' => $this->request->getData('custcode'),
                    'communityId' => $this->request->getData('ref1val1'),
                    'productName' => $product->description,
                    'productId' => $product->id
                ]]);
                $this->eventManager()->dispatch($event);
            }
        }

        $this->viewBuilder()->setLayout('blank');
    }
}
