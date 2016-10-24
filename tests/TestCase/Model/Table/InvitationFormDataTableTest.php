<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\InvitationFormDataTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\InvitationFormDataTable Test Case
 */
class InvitationFormDataTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\InvitationFormDataTable
     */
    public $InvitationFormData;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.invitation_form_data',
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
        $config = TableRegistry::exists('InvitationFormData') ? [] : ['className' => 'App\Model\Table\InvitationFormDataTable'];
        $this->InvitationFormData = TableRegistry::get('InvitationFormData', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->InvitationFormData);

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
