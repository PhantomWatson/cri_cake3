<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DeliverablesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DeliverablesTable Test Case
 */
class DeliverablesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\DeliverablesTable
     */
    public $Deliverables;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Deliverables',
        'app.Deliveries'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Deliverables') ? [] : ['className' => 'App\Model\Table\DeliverablesTable'];
        $this->Deliverables = TableRegistry::get('Deliverables', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Deliverables);

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
}
