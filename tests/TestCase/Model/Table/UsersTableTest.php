<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersTable Test Case
 * @property UsersTable $Users
 */
class UsersTableTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Areas',
        'app.Communities',
        'app.Products',
        'app.Purchases',
        'app.Respondents',
        'app.Responses',
        'app.Statistics',
        'app.Surveys',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Users') ? [] : ['className' => 'App\Model\Table\UsersTable'];
        $this->Users = TableRegistry::get('Users', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Users);

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

    /**
     * Tests UsersTable::getAdminEmailRecipients
     *
     * @return void
     */
    public function testGetAlertRecipients()
    {
        $results = $this->Users->getAdminEmailRecipients('ICI');
        $expected = 1;
        $actual = $results->first()->id;
        $this->assertEquals($expected, $actual);

        $results = $this->Users->getAdminEmailRecipients('CBER');
        $actual = $results->first()->id;
        $this->assertEquals($expected, $actual);

        $results = $this->Users->getAdminEmailRecipients('both');
        $actual = $results->first()->id;
        $this->assertEquals($expected, $actual);
    }
}
