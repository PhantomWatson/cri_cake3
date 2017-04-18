<?php
namespace App\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\CommunitiesController Test Case
 */
class CommunitiesControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.areas',
        'app.communities',
        'app.products',
        'app.purchases',
        'app.respondents',
        'app.responses',
        'app.stat_categories',
        'app.statistics',
        'app.surveys',
        'app.users'
    ];

    /**
     * Sets up this set of tests
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
     * Test for /communities/index
     *
     * @return void
     */
    public function testIndex()
    {
        $this->get('/communities');
        $this->assertResponseOk();
    }

    /**
     * Test for /communities/view
     *
     * @return void
     */
    public function testView()
    {
        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->find('all')->first();
        $this->get(Router::url([
            'controller' => 'Communities',
            'action' => 'view',
            $community->id
        ]));
        $this->assertResponseOk();
    }

    /**
     * Test for /communities/autocomplete
     *
     * @return void
     */
    public function testAutocomplete()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/activate
     *
     * @return void
     */
    public function testAdminActivate()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/add
     *
     * @return void
     */
    public function testAdminAdd()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/add-client
     *
     * @return void
     */
    public function testAdminAddClient()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/alignment-calc-settings
     *
     * @return void
     */
    public function testAdminAlignmentCalcSettings()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/clienthome
     *
     * @return void
     */
    public function testAdminClienthome()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/clients
     *
     * @return void
     */
    public function testAdminClients()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/delete
     *
     * @return void
     */
    public function testAdminDelete()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/edit
     *
     * @return void
     */
    public function testAdminEdit()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/index
     *
     * @return void
     */
    public function testAdminIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/notes
     *
     * @return void
     */
    public function testAdminNotes()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/presentations
     *
     * @return void
     */
    public function testAdminPresentations()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/progress
     *
     * @return void
     */
    public function testAdminProgress()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/remove-client
     *
     * @return void
     */
    public function testAdminRemoveClient()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/select-client
     *
     * @return void
     */
    public function testAdminSelectClient()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/communities/to-do
     *
     * @return void
     */
    public function testAdminToDo()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /client/communities/index
     *
     * @return void
     */
    public function testClientIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /client/communities/reactivate
     *
     * @return void
     */
    public function testClientReactivate()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
