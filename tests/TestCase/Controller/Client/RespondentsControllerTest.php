<?php
namespace App\Test\TestCase\Controller\Client;

use App\Test\TestCase\ApplicationTest;

/**
 * App\Controller\RespondentsController Test Case
 */
class RespondentsControllerTest extends ApplicationTest
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
     * Test for /client/respondents/unapproved
     *
     * @return void
     */
    public function testUnapproved()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /client/respondents/index
     *
     * @return void
     */
    public function testIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /client/respondents/approve-uninvited
     *
     * @return void
     */
    public function testApproveUninvited()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /client/respondents/dismiss-uninvited
     *
     * @return void
     */
    public function testDismissUninvited()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
