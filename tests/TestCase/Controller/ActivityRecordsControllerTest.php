<?php
namespace App\Test\TestCase\Controller;

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

    /**
     * Test /admin/activity-records/index
     *
     * @return void
     */
    public function testAdminIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
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
