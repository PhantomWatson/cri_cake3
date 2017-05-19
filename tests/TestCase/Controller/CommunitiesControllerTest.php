<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\CommunitiesController Test Case
 */
class CommunitiesControllerTest extends ApplicationTest
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.activity_records',
        'app.areas',
        'app.clients_communities',
        'app.communities',
        'app.deliverables',
        'app.deliveries',
        'app.opt_outs',
        'app.products',
        'app.purchases',
        'app.respondents',
        'app.responses',
        'app.settings',
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
        // Publicly-viewable community
        $this->get([
            'controller' => 'Communities',
            'action' => 'view',
            1
        ]);
        $this->assertResponseOk();

        // Not-publicly-viewable community
        $this->get([
            'controller' => 'Communities',
            'action' => 'view',
            2
        ]);
        $this->assertResponseError();
        $this->assertResponseCode(403);
    }

    /**
     * Test for /communities/autocomplete
     *
     * @return void
     */
    public function testAutocomplete()
    {
        $this->get('/communities/autocomplete?term=tes');
        $this->assertResponseOk();
        $this->assertResponseContains('Test Community (public)');

        $this->get('/communities/autocomplete');
        $this->assertResponseError();
    }

    /**
     * Test for /admin/communities/activate
     *
     * @return void
     */
    public function testAdminActivate()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'activate',
            1
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseContains('Mark Test Community (public) inactive');

        // Deactivating
        $this->put($url, [
            'active' => 0
        ]);
        $this->assertResponseSuccess();

        // Checking deactivated community
        $this->get($url);
        $this->assertResponseContains('Reactivate Test Community (public)');

        // Reactivating
        $this->put($url, [
            'active' => 1
        ]);
        $this->assertResponseSuccess();

        // Checking reactivated community
        $this->get($url);
        $this->assertResponseContains('Mark Test Community (public) inactive');
    }

    /**
     * Test for /admin/communities/add
     *
     * @return void
     */
    public function testAdminAdd()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'add'
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();

        // POST
        $data = [
            'name' => 'New Community',
            'local_area_id' => 1,
            'parent_area_id' => 1,
            'score' => 1,
            'public' => 0,
            'intAlignmentAdjustment' => 8.98,
            'intAlignmentThreshold' => 1
        ];
        $this->post($url, $data);
        $this->assertResponseSuccess();
        $communitiesTable = TableRegistry::get('Communities');
        $query = $communitiesTable->find()->where(['name' => $data['name']]);
        $this->assertEquals(1, $query->count());
    }

    /**
     * Test for /admin/communities/add-client
     *
     * @return void
     */
    public function testAdminAddClient()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'addClient',
            1
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();

        // POST
        $data = [
            'salutation' => 'Mr.',
            'name' => 'Test User',
            'title' => 'Test Title',
            'organization' => 'Test Organization',
            'email' => 'test@example.com',
            'phone' => '555-555-5555',
            'unhashed_password' => 'password'
        ];
        $this->post($url, $data);
        $this->assertResponseSuccess();
        $usersTable = TableRegistry::get('Users');
        $query = $usersTable->find()->where([
            'name' => $data['name'],
            'role' => 'client'
        ]);
        $this->assertEquals(1, $query->count());
    }

    /**
     * Test for /admin/communities/alignment-calc-settings
     *
     * @return void
     */
    public function testAdminAlignmentCalcSettings()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'alignmentCalcSettings'
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();
    }

    /**
     * Test for /admin/communities/clienthome
     *
     * @return void
     */
    public function testAdminClienthome()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'clienthome',
            1
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();

        // Invalid community ID
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'clienthome',
            999
        ]);
        $this->get($url);
        $this->assertRedirect([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'index'
        ]);
    }

    /**
     * Test for /admin/communities/clients
     *
     * @return void
     */
    public function testAdminClients()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'clients',
            1
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();

        // Invalid community ID
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'clients',
            999
        ]);
        $this->get($url);
        $this->assertResponseError();
    }

    /**
     * Test for /admin/communities/delete
     *
     * @return void
     */
    public function testAdminDelete()
    {
        $validUrl = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'delete',
            1
        ]);

        // Unauthenticated
        $this->post($validUrl);
        $this->assertRedirectContains(Router::url([
            'prefix' => false,
            'controller' => 'Users',
            'action' => 'login'
        ]));

        // Authenticated
        $this->session($this->adminUser);

        // GET
        $this->get($validUrl);
        $this->assertResponseError();

        // POST, invalid community
        $invalidUrl = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'delete',
            999
        ]);
        $this->post($invalidUrl);
        $this->assertResponseError();

        // POST, valid community
        $this->post($validUrl);
        $this->assertResponseSuccess();

        // Verify delete
        $communitiesTable = TableRegistry::get('Communities');
        $query = $communitiesTable->find()->where(['id' => 1]);
        $this->assertEquals(0, $query->count());
    }

    /**
     * Test for /admin/communities/edit
     *
     * @return void
     */
    public function testAdminEdit()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'edit',
            1
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();

        // Invalid ID
        $this->get(Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'edit',
            999
        ]));
        $this->assertResponseError();

        // POST
        $data = [
            'name' => 'Edited Community',
            'local_area_id' => 2,
            'parent_area_id' => 2,
            'score' => 1,
            'public' => 1,
            'intAlignmentAdjustment' => 9,
            'intAlignmentThreshold' => 2
        ];
        $this->post($url, $data);
        $this->assertResponseSuccess();
        $communitiesTable = TableRegistry::get('Communities');
        $query = $communitiesTable->find()->where(['name' => $data['name']]);
        $this->assertEquals(1, $query->count());
    }

    /**
     * Test for /admin/communities/index
     *
     * @return void
     */
    public function testAdminIndex()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'index'
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseContains('<tr data-community-name="Test Community (public)">');
        $this->assertResponseNotContains('<tr data-community-name="Test Community (inactive)">');

        // Test filters
        $this->get($url . '?filters%5Bstatus%5D=inactive');
        $this->assertResponseContains('<tr data-community-name="Test Community (inactive)">');
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
