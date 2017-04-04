<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class OptOutsController extends AppController
{
    /**
     * Opt-out method
     *
     * @param int $communityId Community ID
     * @param int $productId Product ID
     * @return \Cake\Http\Response
     */
    public function optOut($communityId, $productId)
    {
        $result = $this->OptOuts->addOptOut([
            'user_id' => $this->Auth->user('id'),
            'community_id' => $communityId,
            'product_id' => $productId
        ]);
        if ($result) {
            $this->Flash->success('Opt-out successful');
        } else {
            $msg = 'There was an error opting that community out.';
            $this->Flash->error($msg);
        }

        return $this->redirect($this->request->referer());
    }
}
