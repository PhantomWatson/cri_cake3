<?php
namespace App\Test\TestCase\Controller\Admin;

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
     * Test for /admin/respondents/unapproved
     *
     * @return void
     */
    public function testUnapproved()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/respondents/approve-uninvited
     *
     * @return void
     */
    public function testApproveUninvited()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/respondents/dismiss-uninvited
     *
     * @return void
     */
    public function testDismissUninvited()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/respondents/view
     *
     * @return void
     */
    public function testView()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
