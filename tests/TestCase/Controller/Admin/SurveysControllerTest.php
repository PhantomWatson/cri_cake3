<?php
namespace App\Test\TestCase\Controller\Admin;

use App\Model\Table\SurveysTable;
use App\Test\TestCase\ApplicationTest;
use Cake\Event\EventList;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\SurveysController Test Case
 *
 * @property SurveysTable $Surveys
 */
class SurveysControllerTest extends ApplicationTest
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.activity_records',
        'app.areas',
        'app.communities',
        'app.products',
        'app.purchases',
        'app.queued_jobs',
        'app.respondents',
        'app.responses',
        'app.statistics',
        'app.surveys',
        'app.users',
    ];

    /**
     * SetUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->configRequest([
            'environment' => ['HTTPS' => 'on']
        ]);

        $this->Surveys = TableRegistry::get('Surveys');
        $this->Surveys->getEventManager()->setEventList(new EventList());
    }

    /**
     * Test for /admin/surveys/activate
     *
     * @return void
     */
    public function testActivate()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/surveys/clear-responses
     *
     * @return void
     */
    public function testClearResponses()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/surveys/import-all
     *
     * @return void
     */
    public function testImportAll()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/surveys/invite
     *
     * @return void
     */
    public function testInvite()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/surveys/link
     *
     * @return void
     */
    public function testLink()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/surveys/populate-aware-fields
     *
     * @return void
     */
    public function testPopulateAwareFields()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/surveys/remind
     *
     * @return void
     */
    public function testRemind()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/surveys/resend-invitations
     *
     * @return void
     */
    public function testResendInvitations()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/surveys/update-alignment
     *
     * @return void
     */
    public function testUpdateAlignment()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/surveys/view
     *
     * @return void
     */
    public function testView()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Tests that the correct event is fired after deactivating a survey
     *
     * @return void
     */
    public function testDeactivateEvent()
    {
        $this->session($this->adminUser);
        $surveyId = 1;
        $url = [
            'prefix' => 'admin',
            'controller' => 'Surveys',
            'action' => 'activate',
            $surveyId
        ];
        $data = ['active' => false];
        $this->put($url, $data);
        $this->assertEventFired('Model.Survey.afterDeactivate', $this->_controller->getEventManager());
    }
}
