<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Client;

use App\Test\TestCase\ApplicationTest;

/**
 * App\Controller\SurveysController Test Case
 *
 * @uses \App\Controller\Client\SurveysController
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
        'app.QueuedJobs',
        'app.Respondents',
        'app.Responses',
        'app.Statistics',
        'app.Surveys',
        'app.Users',
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
