<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\OptOutsTable Test Case
 */
class OptOutsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\OptOutsTable
     */
    public $OptOuts;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.OptOuts',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('OptOuts') ? [] : ['className' => 'App\Model\Table\OptOutsTable'];
        $this->OptOuts = TableRegistry::get('OptOuts', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->OptOuts);

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
