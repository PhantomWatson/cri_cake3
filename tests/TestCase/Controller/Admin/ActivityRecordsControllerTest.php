<?php
namespace App\Test\TestCase\Controller\Admin;

use App\Test\TestCase\ApplicationTest;
use Cake\Routing\Router;

/**
 * App\Controller\ActivityRecordsController Test Case
 */
class ActivityRecordsControllerTest extends ApplicationTest
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.activity_records',
        'app.users',
        'app.communities',
        'app.surveys'
    ];

    /**
     * Sets up this group of tests
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
     * Tests that /admin/activity-records/index cannot be accessed when not logged in
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testIndexFailNotLoggedIn()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'ActivityRecords',
            'action' => 'index'
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);
    }

    /**
     * Tests successfully accessing /admin/activity-records/index
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testIndexSuccess()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'ActivityRecords',
            'action' => 'index'
        ]);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();
    }

    /**
     * Tests that /admin/activity-records/community cannot be accessed when not logged in
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testCommunityFailNotLoggedIn()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'ActivityRecords',
            'action' => 'community',
            1
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);
    }

    /**
     * Tests successfully accessing /admin/activity-records/community
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testCommunitySuccess()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'ActivityRecords',
            'action' => 'community',
            1
        ]);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseContains('Community added');
        $this->assertResponseContains('Test Community (public)');
        $this->assertResponseContains('Test Admin');
    }
}
