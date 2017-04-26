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
        $url = '/admin/communities/activate/1';

        // Unauthenticated
        $this->get($url);
        $this->assertRedirectContains(Router::url([
            'prefix' => false,
            'controller' => 'Users',
            'action' => 'login'
        ]));

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
