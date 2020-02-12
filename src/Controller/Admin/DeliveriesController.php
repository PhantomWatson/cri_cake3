<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
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
                'Users',
            ],
            'order' => ['created' => 'DESC'],
        ];
        if (! $this->request->getQuery('show-dummy')) {
            $this->paginate['conditions'] = ['Communities.dummy' => 0];
        }

        $this->set([
            'deliveries' => $this->paginate($this->Deliveries),
            'titleForLayout' => 'Deliverables',
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
                'Users',
            ],
            'order' => ['created' => 'DESC'],
            'conditions' => ['Deliveries.community_id' => $communityId],
        ];
        $communitiesTable = TableRegistry::getTableLocator()->get('Communities');
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
            'titleForLayout' => "Deliveries: $communityName",
        ]);
    }

    /**
     * Add method
     *
     * @param int|null $communityId Community ID
     * @param int|null $deliverableId Deliverable ID
     * @return \Cake\Http\Response|null
     */
    public function add($communityId = null, $deliverableId = null)
    {
        $deliverablesTable = TableRegistry::getTableLocator()->get('Deliverables');
        $delivery = $this->Deliveries->newEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['user_id'] = $this->Auth->user('id');
            $delivery = $this->Deliveries->patchEntity($delivery, $data);
            $errors = $delivery->getErrors();
            if ($errors || ! $this->Deliveries->save($delivery)) {
                $this->Flash->error('There was an error reporting that delivery.');
            } else {
                $this->Flash->success('Delivery reported');

                // Dispatch event
                $deliverable = $deliverablesTable->get($delivery->deliverable_id);
                $event = new Event('Model.Delivery.afterAdd', $this, ['meta' => [
                    'communityId' => $delivery->community_id,
                    'deliverableId' => $delivery->deliverable_id,
                    'deliverableName' => $deliverable->name,
                ]]);
                $this->getEventManager()->dispatch($event);

                return $this->redirect([
                    'prefix' => 'admin',
                    'controller' => 'Deliveries',
                    'action' => 'index',
                ]);
            }
        } elseif ($communityId) {
            $delivery->community_id = $communityId;
            $delivery->deliverable_id = $deliverableId;
        }

        $communitiesTable = TableRegistry::getTableLocator()->get('Communities');
        $this->set([
            'delivery' => $delivery,
            'deliverables' => $deliverablesTable->find('list'),
            'communities' => $communitiesTable->find('list')->order(['name' => 'ASC']),
            'titleForLayout' => 'Report Delivery',
        ]);

        return null;
    }
}
