<?php
namespace App\Test\TestCase\Controller;

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
        'app.queued_jobs',
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
     * Test for /users/login
     *
     * @return void
     */
    public function testLogin()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /users/logout
     *
     * @return void
     */
    public function testLogout()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /users/change-password
     *
     * @return void
     */
    public function testChangePassword()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /users/update-contact
     *
     * @return void
     */
    public function testUpdateContact()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /users/forgot-password
     *
     * @return void
     */
    public function testForgotPassword()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /users/reset-password
     *
     * @return void
     */
    public function testResetPassword()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
