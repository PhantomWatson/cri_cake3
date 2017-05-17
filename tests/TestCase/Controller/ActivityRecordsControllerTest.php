<?php
namespace App\Test\TestCase\Controller;

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
     * Test /admin/activity-records/index
     *
     * @return void
     */
    public function testAdminIndex()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'ActivityRecords',
            'action' => 'index'
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();
    }

    /**
     * Test /admin/activity-records/community
     *
     * @return void
     */
    public function testAdminCommunity()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'ActivityRecords',
            'action' => 'community',
            1
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseContains('Community added');
        $this->assertResponseContains('Test Community (public)');
        $this->assertResponseContains('Test Admin');
    }
}
