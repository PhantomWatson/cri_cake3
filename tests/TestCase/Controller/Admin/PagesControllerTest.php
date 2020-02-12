<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use App\Test\TestCase\ApplicationTest;

/**
 * App\Controller\PagesController Test Case
 */
class PagesControllerTest extends ApplicationTest
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [];

    /**
     * Sets up this set of tests
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->configRequest([
            'environment' => ['HTTPS' => 'on'],
        ]);
    }

    /**
     * Test for /admin/pages/guide
     *
     * @return void
     */
    public function testAdminGuide()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
