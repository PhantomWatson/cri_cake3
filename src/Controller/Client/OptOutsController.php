<?php
namespace App\Controller\Client;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class OptOutsController extends AppController
{
    /**
     * Opt-out method
     *
     * @param int $productId Product ID
     * @return \Cake\Http\Response
     */
    public function optOut($productId)
    {
        $clientId = $this->Auth->user('id');
        $communitiesTable = TableRegistry::get('Communities');
        $communityId = $communitiesTable->getClientCommunityId($clientId);
        $result = $this->OptOuts->addOptOut([
            'user_id' => $clientId,
            'community_id' => $communityId,
            'product_id' => $productId
        ]);
        if ($result) {
            $this->Flash->success('Opt-out successful');
        } else {
            $msg = 'There was an error opting you out. Please contact an administrator for assistance.';
            $this->Flash->error($msg);
        }

        return $this->redirect([
            'prefix' => 'client',
            'controller' => 'Communities',
            'action' => 'index'
        ]);
    }
}
