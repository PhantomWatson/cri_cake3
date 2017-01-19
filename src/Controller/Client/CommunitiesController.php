<?php
namespace App\Controller\Client;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class CommunitiesController extends AppController
{
    /**
     * Client home page
     *
     * @return \App\Controller\Response
     */
    public function index()
    {
        $this->viewBuilder()->helpers(['ClientHome']);
        $userId = $this->Auth->user('id');
        $communityId = $this->Communities->getClientCommunityId($userId);
        $this->loadComponent('ClientHome');
        $prepResult = $this->ClientHome->prepareClientHome($communityId);
        if (! $prepResult) {
            $this->set('titleForLayout', 'CRI Account Not Yet Ready For Use');

            return $this->render('notready');
        }
    }
}
