<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ActivityRecordsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ActivityRecordsTable Test Case
 */
class ActivityRecordsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\ActivityRecordsTable
     */
    public $ActivityRecords;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ActivityRecords',
        'app.Users',
        'app.Communities',
        'app.Surveys'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('ActivityRecords') ? [] : ['className' => 'App\Model\Table\ActivityRecordsTable'];
        $this->ActivityRecords = TableRegistry::get('ActivityRecords', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->ActivityRecords);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
