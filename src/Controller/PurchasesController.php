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
        if (isset($_POST['respmessage']) && $_POST['respmessage'] == 'SUCCESS') {
            $itemCode = explode('-', $_POST['itemcode1']);
            $productsTable = TableRegistry::get('Products');
            $this->Purchases->newEntity([
                'user_id' => $_POST['custcode'],
                'community_id' => $_POST['ref1val1'],
                'product_id' => $productsTable->getIdFromItemCode($itemCode[1]),
                'postback' => base64_encode(serialize($_POST))
            ]);
            $this->Purchase->save();
        }

        $this->viewBuilder()->layout('blank');
    }
}
