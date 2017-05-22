<?php
namespace App\Test\TestCase\Controller\Client;

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
        'app.areas',
        'app.communities',
        'app.products',
        'app.purchases',
        'app.respondents',
        'app.responses',
        'app.statistics',
        'app.surveys',
        'app.users'
    ];

    /**
     * Test for /client/surveys/invite
     *
     * @return void
     */
    public function testInvite()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /client/surveys/remind
     *
     * @return void
     */
    public function testRemind()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /client/surveys/upload-invitation-spreadsheet
     *
     * @return void
     */
    public function testUploadInvitationSpreadsheet()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
