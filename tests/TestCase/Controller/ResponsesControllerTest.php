<?php
namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\ResponsesController Test Case
 */
class ResponsesControllerTest extends IntegrationTestCase
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
    public function testAdminCalculateMissingAlignments()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/responses/view
     *
     * @return void
     */
    public function testAdminView()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/responses/get-full-response
     *
     * @return void
     */
    public function testAdminGetFullResponse()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
