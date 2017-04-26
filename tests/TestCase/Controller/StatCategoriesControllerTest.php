<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

/**
 * App\Controller\StatCategoriesController Test Case
 */
class StatCategoriesControllerTest extends ApplicationTest
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.stat_categories',
        'app.statistics'
    ];

    /**
     * Test for /admin/stat-categories/import
     *
     * @return void
     */
    public function testAdminImport()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
