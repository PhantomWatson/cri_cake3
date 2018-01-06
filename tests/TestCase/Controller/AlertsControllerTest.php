<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\AlertsController Test Case
 */
class AlertsControllerTest extends ApplicationTest
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.clients_communities',
        'app.communities',
        'app.queued_jobs',
        'app.surveys',
        'app.users',
    ];

    /**
     * Sets up this set of tests
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->configRequest([
            'environment' => ['HTTPS' => 'on']
        ]);
    }

    /**
     * Tests /alerts/check-no-client-assigned
     *
     * @return void
     */
    public function testAssignClientAlert()
    {
        $this->get([
            'controller' => 'Alerts',
            'action' => 'checkNoClientAssigned'
        ]);

        $queuedJobsTable = TableRegistry::get('Queue.QueuedJobs');
        $queuedJob = $queuedJobsTable->find()->first();
        $this->assertNotEmpty($queuedJob);
    }

    /**
     * Tests /alerts/check-no-officials-survey
     *
     * @return void
     */
    public function testCreateOfficialsSurveyAlert()
    {
        $queuedJobsTable = TableRegistry::get('Queue.QueuedJobs');

        // Test condition where no alerts are needed
        $this->get([
            'controller' => 'Alerts',
            'action' => 'checkNoOfficialsSurvey'
        ]);
        $this->assertEmpty($queuedJobsTable->find()->first());

        // Test condition where alerts are needed
        $surveysTable = TableRegistry::get('Surveys');
        $surveys = $surveysTable->find()->where(['community_id' => 1])->all();
        foreach ($surveys as $survey) {
            $survey->community_id = 2;
            $surveysTable->save($survey);
        }
        $this->get([
            'controller' => 'Alerts',
            'action' => 'checkNoOfficialsSurvey'
        ]);
        $this->assertNotEmpty($queuedJobsTable->find()->first());
    }
}
