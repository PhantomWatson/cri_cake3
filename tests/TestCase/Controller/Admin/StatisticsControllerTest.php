<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use App\Test\TestCase\ApplicationTest;

/**
 * App\Controller\StatisticsController Test Case
 *
 * @uses \App\Controller\Admin\StatisticsController
 */
class StatisticsControllerTest extends ApplicationTest
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
        'app.StatCategories',
        'app.Statistics',
        'app.Surveys',
        'app.Users',
    ];

    /**
     * Test for /admin/statistics/import
     *
     * @return void
     */
    public function testImport()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/statistics/import-grouped
     *
     * @return void
     */
    public function testImportGrouped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
