<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;

/**
 * Deliveries Controller
 *
 * @property \App\Model\Table\DeliveriesTable $Deliveries
 */
class DeliveriesController extends AppController
{
    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => [
                'Communities',
                'Deliverables',
                'Users'
            ],
            'order' => ['created' => 'DESC']
        ];
        if (! $this->request->getQuery('show-dummy')) {
            $this->paginate['conditions'] = ['Communities.dummy' => 0];
        }

        $this->set([
            'deliveries' => $this->paginate($this->Deliveries),
            'titleForLayout' => 'Deliveries'
        ]);
    }

    /**
     * Shows deliveries associated with the specified community
     *
     * @param int $communityId Community ID
     * @return void
     */
    public function community($communityId)
    {
        $this->paginate = [
            'contain' => [
                'Communities',
                'Deliverables',
                'Users'
            ],
            'order' => ['created' => 'DESC'],
            'conditions' => ['Deliveries.community_id' => $communityId]
        ];
        $communitiesTable = TableRegistry::get('Communities');
        $communityName = null;
        $deliveries = $this->paginate($this->Deliveries);
        try {
            $community = $communitiesTable->get($communityId);
            $communityName = $community->name;
        } catch (RecordNotFoundException $e) {
            $communityName = "Community #$communityId";
        }

        $this->set([
            'deliveries' => $deliveries,
            'communityId' => $communityId,
            'titleForLayout' => "Deliveries: $communityName"
        ]);
    }
}
