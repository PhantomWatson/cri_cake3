<?php
namespace App\Test\TestCase\Controller;

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
    public function testAdminUnapproved()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/respondents/approve-uninvited
     *
     * @return void
     */
    public function testAdminApproveUninvited()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/respondents/dismiss-uninvited
     *
     * @return void
     */
    public function testAdminDismissUninvited()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/respondents/view
     *
     * @return void
     */
    public function testAdminView()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /client/respondents/unapproved
     *
     * @return void
     */
    public function testClientUnapproved()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /client/respondents/index
     *
     * @return void
     */
    public function testClientIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /client/respondents/approve-uninvited
     *
     * @return void
     */
    public function testClientApproveUninvited()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /client/respondents/dismiss-uninvited
     *
     * @return void
     */
    public function testClientDismissUninvited()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
