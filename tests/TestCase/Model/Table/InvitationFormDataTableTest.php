<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

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
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::exists('InvitationFormData') ? [] : ['className' => 'App\Model\Table\InvitationFormDataTable'];
        $this->InvitationFormData = TableRegistry::getTableLocator()->get('InvitationFormData', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
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
