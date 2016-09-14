<?php
namespace App\Controller\Client;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class PurchasesController extends AppController
{
    /**
     * Index method
     *
     * @return \App\Controller\Response
     */
    public function index()
    {
        $communitiesTable = TableRegistry::get('Communities');
        $clientId = $this->getClientId();
        if (! $clientId) {
            return $this->chooseClientToImpersonate();
        }
        $communityId = $communitiesTable->getClientCommunityId($clientId);

        $productsTable = TableRegistry::get('Products');
        $products = $productsTable->find('all')->toArray();

        foreach ($products as &$product) {
            // Massage product data
            $product->description = str_ireplace('PwR3', 'PWR<sup>3</sup>', $product->description);
            $product->price = '$' . number_format($product->price, 0);
            $product->status = $productsTable->getPurchaseStatus($communityId, $product->id, $clientId);
        }

        $community = $communitiesTable->get($communityId);
        $this->set([
            'titleForLayout' => 'Products Purchased for ' . $community->name,
            'products' => $products
        ]);
    }
}
