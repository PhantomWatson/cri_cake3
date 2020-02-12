<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;

/**
 * ActivityRecords Controller
 *
 * @property \App\Model\Table\ActivityRecordsTable $ActivityRecords
 */
class ActivityRecordsController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     * @throws \Exception
     */
    public function initialize()
    {
        parent::initialize();
        $this->viewBuilder()->setHelpers(['ActivityRecords']);
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Users', 'Communities', 'Surveys'],
            'order' => [
                'created' => 'DESC',
                'id' => 'DESC',
            ],
        ];
        if (! $this->request->getQuery('show-dummy')) {
            $this->paginate['conditions'] = ['dummy' => 0];
        }

        $trackedEvents = [
            'Community added or deleted',
            'User account added or deleted',
            'Questionnaire\'s link to SurveyMonkey created or updated',
            'Questionnaire activated or deactivated',
            'Purchase made',
            'Purchase record added by admin',
            'Refund recorded',
            'Responses imported',
            'Community manually promoted to next step or demoted to previous step',
            'Community automatically promoted to next step',
            'Uninvited respondents approved or dismissed',
            'Invitations sent',
            'Reminders sent',
            'Delivery recorded',
            'Client added to or removed from community',
        ];
        $this->set([
            'activityRecords' => $this->paginate($this->ActivityRecords),
            'titleForLayout' => 'Activity Log',
            'trackedEvents' => $trackedEvents,
        ]);
        $this->set('_serialize', ['activityRecords']);
    }

    /**
     * Shows activity records associated with the specified community
     *
     * @param int $communityId Community ID
     * @return void
     */
    public function community($communityId)
    {
        $this->paginate = [
            'contain' => ['Users', 'Communities', 'Surveys'],
            'order' => ['created' => 'DESC'],
            'conditions' => ['ActivityRecords.community_id' => $communityId],
        ];
        $communitiesTable = TableRegistry::get('Communities');
        $communityName = null;
        $activityRecords = $this->paginate($this->ActivityRecords);
        try {
            $community = $communitiesTable->get($communityId);
            $communityName = $community->name;
        } catch (RecordNotFoundException $e) {
            foreach ($activityRecords as $record) {
                if (strpos($record->meta, 'communityName') !== false) {
                    $meta = unserialize($record->meta);
                    $communityName = $meta['communityName'];
                    break;
                }
            }
        }

        $this->set([
            'activityRecords' => $activityRecords,
            'communityId' => $communityId,
            'titleForLayout' => 'Activity Log: ' . ($communityName ?: "Community #$communityId"),
        ]);
        $this->set('_serialize', ['activityRecords']);
    }
}
