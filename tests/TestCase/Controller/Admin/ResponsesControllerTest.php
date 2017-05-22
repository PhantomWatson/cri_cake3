<?php
namespace App\Test\TestCase\Controller\Admin;

use App\Test\TestCase\ApplicationTest;

/**
 * App\Controller\ResponsesController Test Case
 */
class ResponsesControllerTest extends ApplicationTest
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
     * Test for /admin/responses/calculate-missing-alignments
     *
     * @return void
     */
    public function testCalculateMissingAlignments()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/responses/view
     *
     * @return void
     */
    public function testView()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/responses/get-full-response
     *
     * @return void
     */
    public function testGetFullResponse()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
