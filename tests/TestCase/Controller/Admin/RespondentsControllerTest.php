<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use App\Test\TestCase\ApplicationTest;

/**
 * App\Controller\RespondentsController Test Case
 *
 * @uses \App\Controller\Admin\RespondentsController
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
        'app.Users',
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
