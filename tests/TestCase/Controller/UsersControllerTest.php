<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

/**
 * App\Controller\UsersController Test Case
 *
 * @uses \App\Controller\UsersController
 */
class UsersControllerTest extends ApplicationTest
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
        'app.QueuedJobs',
        'app.Respondents',
        'app.Responses',
        'app.Statistics',
        'app.Surveys',
        'app.Users',
    ];

    /**
     * Sets up this set of tests
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->configRequest([
            'environment' => ['HTTPS' => 'on'],
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
