<?php
declare(strict_types=1);

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
