<?php
namespace App\Test\TestCase\Controller;

use App\Test\Fixture\UsersFixture;
use App\Test\TestCase\ApplicationTest;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

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
     * Test for /client/communities/index
     *
     * @return void
     */
    public function testClientIndex()
    {
        $url = Router::url([
            'prefix' => 'client',
            'controller' => 'Communities',
            'action' => 'index'
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $this->session($this->clientUser);
        $this->get($url);
        $this->assertResponseOk();
    }

    /**
     * Test for /client/communities/reactivate
     *
     * @return void
     */
    public function testClientReactivate()
    {
        $url = Router::url([
            'prefix' => 'client',
            'controller' => 'Communities',
            'action' => 'reactivate'
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $usersFixture = new UsersFixture();
        $clientSession = [
            'Auth' => [
                // Client account associated with inactive community
                'User' => $usersFixture->records[2]
            ]
        ];
        $this->session($clientSession);
        $this->get($url);
        $this->assertResponseOk();

        // Confirm community is inactive
        $communitiesTable = TableRegistry::get('Communities');
        $query = $communitiesTable->find()->where([
            'id' => 3,
            'active' => 0
        ]);
        $this->assertEquals(1, $query->count());

        // PUT
        $this->put($url, []);

        // Confirm community has been reactivated
        $query = $communitiesTable->find()->where([
            'id' => 3,
            'active' => 1
        ]);
        $this->assertEquals(1, $query->count());
    }
}
