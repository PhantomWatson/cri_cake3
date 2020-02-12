<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

/**
 * App\Controller\SurveysController Test Case
 */
class SurveysControllerTest extends ApplicationTest
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Areas',
        'app.Communities',
        'app.Products',
        'app.Purchases',
        'app.Respondents',
        'app.Responses',
        'app.Statistics',
        'app.Surveys',
        'app.Users'
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
     * Test for /surveys/check-survey-assignment
     *
     * @return void
     */
    public function testCheckSurveyAssignment()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /surveys/clear-saved-invitation-data
     *
     * @return void
     */
    public function testClearSavedInvitationData()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /surveys/cron-import
     *
     * @return void
     */
    public function testCronImport()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /surveys/get-qna-ids
     *
     * @return void
     */
    public function testGetQnaIds()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /surveys/get-survey-list
     *
     * @return void
     */
    public function testGetSurveyList()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /surveys/get-survey-url
     *
     * @return void
     */
    public function testGetSurveyUrl()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /surveys/import
     *
     * @return void
     */
    public function testImport()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
