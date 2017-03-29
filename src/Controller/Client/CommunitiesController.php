<?php
namespace App\Controller\Client;

use App\Controller\AppController;

class CommunitiesController extends AppController
{
    /**
     * Client home page
     *
     * @return \App\Controller\Response|void
     */
    public function index()
    {
        $this->viewBuilder()->helpers(['ClientHome']);

        $userId = $this->Auth->user('id');
        $communityId = $this->Communities->getClientCommunityId($userId);
        if ($communityId) {
            $this->loadComponent('ClientHome');
            if ($this->ClientHome->prepareClientHome($communityId)) {
                return;
            }
        }

        $this->set('titleForLayout', 'CRI Account Not Yet Ready For Use');

        return $this->render('notready');
    }
}
