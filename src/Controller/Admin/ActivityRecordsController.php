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

        $this->set([
            'activityRecords' => $activityRecords,
            'titleForLayout' => 'Activity Log'
        ]);
        $this->set('_serialize', ['activityRecords']);
    }
}
