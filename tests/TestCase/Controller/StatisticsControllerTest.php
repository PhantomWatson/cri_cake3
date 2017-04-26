<?php
namespace App\Test\TestCase\Controller;

use App\Controller\StatisticsController;
use App\Test\TestCase\ApplicationTest;

/**
 * App\Controller\StatisticsController Test Case
 */
class StatisticsControllerTest extends ApplicationTest
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
        'app.stat_categories',
        'app.statistics',
        'app.surveys',
        'app.users'
    ];

    /**
     * Test for /admin/statistics/import
     *
     * @return void
     */
    public function testAdminImport()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/statistics/import-grouped
     *
     * @return void
     */
    public function testAdminImportGrouped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
