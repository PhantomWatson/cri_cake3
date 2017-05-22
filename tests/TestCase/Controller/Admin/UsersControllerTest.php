<?php
namespace App\Test\TestCase\Controller\Admin;

use App\Test\TestCase\ApplicationTest;

/**
 * App\Controller\UsersController Test Case
 */
class UsersControllerTest extends ApplicationTest
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
     * Sets up this set of tests
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->configRequest([
            'environment' => ['HTTPS' => 'on']
        ]);
    }

    /**
     * Test for /admin/users/index
     *
     * @return void
     */
    public function testIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/users/add
     *
     * @return void
     */
    public function testAdd()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/users/edit
     *
     * @return void
     */
    public function testEdit()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/users/delete
     *
     * @return void
     */
    public function testDelete()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/users/choose-client
     *
     * @return void
     */
    public function testChooseClient()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
