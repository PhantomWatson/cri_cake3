<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * ActivityRecords Controller
 *
 * @property \App\Model\Table\ActivityRecordsTable $ActivityRecords
 */
class ActivityRecordsController extends AppController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->viewBuilder()->helpers(['ActivityRecords']);
        $this->paginate = [
            'contain' => ['Users', 'Communities', 'Surveys'],
            'order' => ['created' => 'DESC']
        ];
        $activityRecords = $this->paginate($this->ActivityRecords);
        $trackedEvents = [
            'Community added',
            'User account added',
            'Questionnaire\'s link to SurveyMonkey created or updated',
            'Questionnaire activated or deactivated',
            'Purchase made',
            'Purchase record added by admin',
            'Refund recorded',
            'Responses imported',
            'Community promoted to next step or demoted to previous step',
            'Uninvited respondents approved or dismissed',
            'Invitations sent'
        ];
        $this->set([
            'activityRecords' => $activityRecords,
            'titleForLayout' => 'Activity Log',
            'trackedEvents' => $trackedEvents
        ]);
        $this->set('_serialize', ['activityRecords']);
    }
}
