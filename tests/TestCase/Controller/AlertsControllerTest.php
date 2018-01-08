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
        'app.responses',
        'app.surveys',
        'app.users'
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

        $this->assertAdminTaskEmailEnqueued('assignClient');
    }

    /**
     * Tests /alerts/check-no-officials-survey
     *
     * @return void
     */
    public function testCreateOfficialsSurveyAlert()
    {
        // Test condition where no alerts are needed
        $this->get([
            'controller' => 'Alerts',
            'action' => 'checkNoOfficialsSurvey'
        ]);
        $this->assertAdminTaskEmailNotEnqueued('createSurveyNewCommunity');

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
        $this->assertAdminTaskEmailEnqueued('createSurveyNewCommunity');
    }

    /**
     * Tests /alerts/check-survey-not-activated
     *
     * @return void
     */
    public function testActivateSurveyAlert()
    {
        $url = [
            'controller' => 'Alerts',
            'action' => 'checkSurveyNotActivated'
        ];

        // Test condition where no alerts are needed
        $this->get($url);
        $this->assertAdminTaskEmailNotEnqueued('activateSurvey');

        // Test condition where alerts are needed
        $surveysTable = TableRegistry::get('Surveys');
        $responsesTable = TableRegistry::get('Responses');
        $surveys = $surveysTable->find()->where(['community_id' => 1])->all();
        foreach ($surveys as $survey) {
            $survey->active = false;
            $surveysTable->save($survey);
            $responses = $responsesTable->find()
                ->where(['survey_id' => $survey->id])
                ->all();
            foreach ($responses as $response) {
                $responsesTable->delete($response);
            }
        }
        $this->get($url);
        $this->assertAdminTaskEmailEnqueued('activateSurvey');
    }
}
