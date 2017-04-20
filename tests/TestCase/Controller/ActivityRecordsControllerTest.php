<?php
namespace App\Test\TestCase\Controller;

use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\ActivityRecordsController Test Case
 */
class ActivityRecordsControllerTest extends IntegrationTestCase
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

    public $adminUser = [
        'Auth' => [
            'User' => [
                'id' => 1,
                'role' => 'admin',
                'salutation' => '',
                'name' => 'Test Admin',
                'email' => 'testadmin@example.com',
                'phone' => '765-285-3399',
                'title' => 'Test',
                'organization' => 'Test',
                'password' => '$2y$10$oaedH1cbAvt/wayrRJlrVeMWtoQSzgBee81iivOmw4tjMlnvfdP/a',
                'all_communities' => true,
                'created' => '2013-10-16 11:49:38',
                'modified' => '2016-01-26 01:06:26'
            ]
        ]
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
        $url = '/admin/activity-records/index';

        // Unauthenticated
        $this->get($url);
        $this->assertRedirectContains(Router::url([
            'prefix' => false,
            'controller' => 'Users',
            'action' => 'login'
        ]));

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
        $this->markTestIncomplete('Not implemented yet.');
    }
}
