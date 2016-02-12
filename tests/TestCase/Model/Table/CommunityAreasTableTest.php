<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CommunityAreasTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CommunityAreasTable Test Case
 */
class CommunityAreasTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\CommunityAreasTable
     */
    public $CommunityAreas;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.community_areas',
        'app.communities',
        'app.area',
        'app.statistics',
        'app.areas',
        'app.stat_categories',
        'app.statistic',
        'app.purchases',
        'app.users',
        'app.consultant_communities',
        'app.surveys',
        'app.respondents',
        'app.responses',
        'app.surveys_backup',
        'app.official_survey',
        'app.organization_survey',
        'app.consultants',
        'app.communities_consultants',
        'app.client_communities',
        'app.clients',
        'app.clients_communities',
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
        $config = TableRegistry::exists('CommunityAreas') ? [] : ['className' => 'App\Model\Table\CommunityAreasTable'];
        $this->CommunityAreas = TableRegistry::get('CommunityAreas', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->CommunityAreas);

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
