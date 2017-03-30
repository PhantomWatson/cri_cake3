<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\OptOutsTable;
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
        'app.opt_outs',
        'app.users',
        'app.purchases',
        'app.communities',
        'app.local_areas',
        'app.statistics',
        'app.areas',
        'app.stat_categories',
        'app.statistic',
        'app.parent_areas',
        'app.surveys',
        'app.respondents',
        'app.responses',
        'app.surveys_backup',
        'app.official_survey',
        'app.organization_survey',
        'app.consultants',
        'app.consultant_communities',
        'app.communities_consultants',
        'app.clients',
        'app.client_communities',
        'app.clients_communities',
        'app.activity_records',
        'app.products',
        'app.refunders'
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
