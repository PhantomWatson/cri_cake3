<?php
declare(strict_types=1);

namespace App\Controller\Client;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

/**
 * @property \App\Model\Table\PurchasesTable $Purchases
 */
class PurchasesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response
     */
    public function index()
    {
        $communitiesTable = TableRegistry::getTableLocator()->get('Communities');
        $clientId = $this->getClientId();
        if (! $clientId) {
            return $this->chooseClientToImpersonate();
        }
        $communityId = $communitiesTable->getClientCommunityId($clientId);

        if (! $communityId) {
            $this->set('titleForLayout', 'CRI Account Not Yet Ready For Use');

            return $this->render('../Communities/notready');
        }

        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $products = $productsTable->find('all')->toArray();

        foreach ($products as &$product) {
            // Massage product data
            $product->description = str_ireplace('PwR3', 'PWR<sup>3</sup>', $product->description);
            $product->price = '$' . number_format($product->price, 0);
            $product->status = $productsTable->getPurchaseStatus($communityId, $product->id, $clientId);
        }

        $community = $communitiesTable->get($communityId);
        $this->set([
            'currentStep' => $community->score,
            'titleForLayout' => 'CRI Products Purchased for ' . $community->name,
            'products' => $products,
        ]);
    }
}
