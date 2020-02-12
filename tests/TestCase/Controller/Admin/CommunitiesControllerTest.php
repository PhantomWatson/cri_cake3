<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use App\Model\Table\ProductsTable;
use App\Test\TestCase\ApplicationTest;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

/**
 * App\Controller\Admin\CommunitiesController Test Case
 *
 * @uses \App\Controller\Admin\CommunitiesController
 */
class CommunitiesControllerTest extends ApplicationTest
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ActivityRecords',
        'app.Areas',
        'app.ClientsCommunities',
        'app.Communities',
        'app.Deliverables',
        'app.Deliveries',
        'app.OptOuts',
        'app.Products',
        'app.Purchases',
        'app.QueuedJobs',
        'app.Respondents',
        'app.Responses',
        'app.Settings',
        'app.StatCategories',
        'app.Statistics',
        'app.Surveys',
        'app.Users',
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
            'environment' => ['HTTPS' => 'on'],
        ]);
    }

    /**
     * Test for /admin/communities/activate
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testActivate()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'activate',
            'test-community-1',
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseContains('Mark Test Community (public) inactive');

        // Deactivating
        $this->put($url, [
            'active' => 0,
        ]);
        $this->assertResponseSuccess();

        // Checking deactivated community
        $this->get($url);
        $this->assertResponseContains('Reactivate Test Community (public)');

        // Reactivating
        $this->put($url, [
            'active' => 1,
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
     * @throws \PHPUnit\Exception
     */
    public function testAdd()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'add',
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
            'intAlignmentThreshold' => 1,
        ];
        $this->post($url, $data);
        $this->assertResponseSuccess();
        $communitiesTable = TableRegistry::getTableLocator()->get('Communities');
        $query = $communitiesTable->find()->where(['name' => $data['name']]);
        $this->assertEquals(1, $query->count());
    }

    /**
     * Test for /admin/communities/add-client
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAddClient()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'addClient',
            1,
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
            'unhashed_password' => 'password',
        ];
        $this->post($url, $data);
        $this->assertResponseSuccess();
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $query = $usersTable->find()->where([
            'name' => $data['name'],
            'role' => 'client',
        ]);
        $this->assertEquals(1, $query->count());
    }

    /**
     * Test for /admin/communities/alignment-calc-settings
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAlignmentCalcSettings()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'alignmentCalcSettings',
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
     * @throws \PHPUnit\Exception
     */
    public function testClienthome()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'clienthome',
            'test-community-1',
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();

        // Invalid community
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'clienthome',
            'invalid-slug',
        ]);
        $this->get($url);
        $this->assertResponseError();
    }

    /**
     * Test for /admin/communities/clients
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testClients()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'clients',
            'test-community-1',
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();

        // Invalid community slug
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'clients',
            'invalid-slug',
        ]);
        $this->get($url);
        $this->assertResponseError();
    }

    /**
     * Test for /admin/communities/delete
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testDelete()
    {
        $validUrl = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'delete',
            1,
        ]);

        // Unauthenticated
        $this->post($validUrl);
        $this->assertRedirectContains(Router::url([
            'prefix' => false,
            'controller' => 'Users',
            'action' => 'login',
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
            999,
        ]);
        $this->post($invalidUrl);
        $this->assertResponseError();

        // POST, valid community
        $this->post($validUrl);
        $this->assertResponseSuccess();

        // Verify delete
        $communitiesTable = TableRegistry::getTableLocator()->get('Communities');
        $query = $communitiesTable->find()->where(['id' => 1]);
        $this->assertEquals(0, $query->count());
    }

    /**
     * Test for /admin/communities/edit
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testEdit()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'edit',
            'test-community-1',
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
            'invalid-slug',
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
            'intAlignmentThreshold' => 2,
        ];
        $this->post($url, $data);
        $this->assertResponseSuccess();
        $communitiesTable = TableRegistry::getTableLocator()->get('Communities');
        $query = $communitiesTable->find()->where(['name' => $data['name']]);
        $this->assertEquals(1, $query->count());
    }

    /**
     * Test for /admin/communities/index
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testIndex()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'index',
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseContains('data-community-name="Test Community (public)"');
    }

    /**
     * Test for /admin/communities/notes
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testNotes()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'notes',
            'test-community-1',
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();

        // POST
        $data = ['notes' => 'New notes'];
        $this->post($url, $data);
        $this->assertResponseOk();
        $this->assertResponseContains('Notes updated');
        $communitiesTable = TableRegistry::getTableLocator()->get('Communities');
        $query = $communitiesTable->find()->where(['notes' => $data['notes']]);
        $this->assertEquals(1, $query->count());
    }

    /**
     * Test for /admin/communities/presentations
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testPresentations()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'presentations',
            'test-community-1',
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();

        // Schedule presentation
        $date = [
            'year' => 2099,
            'month' => 1,
            'day' => 1,
        ];
        $data = [
            'presentation_a_scheduled' => 1,
            'presentation_a' => $date,
            'presentation_b_scheduled' => 0,
            'presentation_b' => $date,
            'presentation_c_scheduled' => 0,
            'presentation_c' => $date,
            'presentation_d_scheduled' => 0,
            'presentation_d' => $date,
        ];
        $this->post($url, $data);
        $this->assertResponseSuccess();
        $communitiesTable = TableRegistry::getTableLocator()->get('Communities');
        $query = $communitiesTable->find()->where([
            'id' => 1,
            'presentation_a' => implode('-', $data['presentation_a']),
        ]);
        $this->assertEquals(1, $query->count());

        // Opt out of presentation
        $data['presentation_b_scheduled'] = 'opted-out';
        $this->post($url, $data);
        $this->assertResponseSuccess();
        $query = $communitiesTable->find()->where([
            'id' => 1,
            function ($exp, $q) {
                return $exp->isNull('presentation_b');
            },
        ]);
        $this->assertEquals(1, $query->count());
        $optOutsTable = TableRegistry::getTableLocator()->get('OptOuts');
        $query = $optOutsTable->find()->where([
            'user_id' => $this->adminUser['Auth']['User']['id'],
            'community_id' => 1,
            'product_id' => ProductsTable::OFFICIALS_SUMMIT,
        ]);
        $this->assertEquals(1, $query->count());
    }

    /**
     * Test for /admin/communities/progress
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testProgress()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'progress',
            'test-community-1',
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();

        // PUT
        $data = ['score' => 2];
        $this->put($url, $data);
        $this->assertResponseSuccess();
        $communitiesTable = TableRegistry::getTableLocator()->get('Communities');
        $query = $communitiesTable->find()->where([
            'id' => 1,
            'score' => $data['score'],
        ]);
        $this->assertEquals(1, $query->count());
    }

    /**
     * Test for /admin/communities/remove-client
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testRemoveClient()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'removeClient',
            2,
            1,
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Confirm existing association
        $clientsCommunitiesTable = TableRegistry::getTableLocator()->get('ClientsCommunities');
        $query = $clientsCommunitiesTable->find()->where([
            'community_id' => 1,
            'client_id' => 2,
        ]);
        $this->assertEquals(1, $query->count());

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseSuccess();

        // Confirm removed association
        $query = $clientsCommunitiesTable->find()->where([
            'community_id' => 1,
            'client_id' => 2,
        ]);
        $this->assertEquals(0, $query->count());
    }

    /**
     * Test for /admin/communities/select-client
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testSelectClient()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'selectClient',
            2,
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();

        // Confirm existing association
        $clientsCommunitiesTable = TableRegistry::getTableLocator()->get('ClientsCommunities');
        $query = $clientsCommunitiesTable->find()->where([
            'community_id' => 1,
            'client_id' => 2,
        ]);
        $this->assertEquals(1, $query->count());

        // POST
        $data = ['client_id' => 3];
        $this->post($url, $data);

        // Confirm new association
        $query = $clientsCommunitiesTable->find()->where([
            'community_id' => 2,
            'client_id' => 3,
        ]);
        $this->assertEquals(1, $query->count());

        // Confirm old association was removed
        $query = $clientsCommunitiesTable->find()->where([
            'community_id' => 3,
            'client_id' => 3,
        ]);
        $this->assertEquals(0, $query->count());
    }

    /**
     * Test for /admin/communities/to-do
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testToDo()
    {
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'toDo',
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->adminUser);
        $this->get($url);
        $this->assertResponseOk();
    }

    /**
     * Tests correct event dispatching when score changes via /admin/communities/edit
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testEditScoreChangeEvent()
    {
        $this->session($this->adminUser);
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'edit',
            'test-community-1',
        ]);

        // Test event for score increase
        $data = ['score' => 2];
        $this->post($url, $data);
        $this->assertEventFired('Model.Community.afterScoreIncrease', $this->_controller->getEventManager());

        // Test event for score decrease
        $data = ['score' => 1];
        $this->post($url, $data);
        $this->assertEventFired('Model.Community.afterScoreDecrease', $this->_controller->getEventManager());
    }

    /**
     * Tests correct event dispatching when score changes via /admin/communities/progress
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testProgressScoreChangeEvent()
    {
        $this->session($this->adminUser);
        $url = Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'progress',
            'test-community-1',
        ]);

        // Test event for score increase
        $data = ['score' => 2];
        $this->put($url, $data);
        $this->assertEventFired('Model.Community.afterScoreIncrease', $this->_controller->getEventManager());

        // Test event for score decrease
        $data = ['score' => 1];
        $this->put($url, $data);
        $this->assertEventFired('Model.Community.afterScoreDecrease', $this->_controller->getEventManager());
    }
}
