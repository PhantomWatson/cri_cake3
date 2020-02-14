<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Client;

use App\Test\Fixture\UsersFixture;
use App\Test\TestCase\ApplicationTest;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

/**
 * App\Controller\Client\CommunitiesController Test Case
 *
 * @uses \App\Controller\Client\CommunitiesController
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
    public function setUp(): void
    {
        parent::setUp();
        $this->configRequest([
            'environment' => ['HTTPS' => 'on'],
        ]);
    }

    /**
     * Test for /client/communities/index
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testIndex()
    {
        $url = Router::url([
            'prefix' => 'client',
            'controller' => 'Communities',
            'action' => 'index',
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
     * @throws \PHPUnit\Exception
     */
    public function testReactivate()
    {
        $url = Router::url([
            'prefix' => 'client',
            'controller' => 'Communities',
            'action' => 'reactivate',
        ]);

        // Unauthenticated
        $this->assertRedirectToLogin($url);

        // Authenticated
        $usersFixture = new UsersFixture();
        $clientSession = [
            'Auth' => [
                // Client account associated with inactive community
                'User' => $usersFixture->records[2],
            ],
        ];
        $this->session($clientSession);
        $this->get($url);
        $this->assertResponseOk();

        // Confirm community is inactive
        $communitiesTable = TableRegistry::getTableLocator()->get('Communities');
        $query = $communitiesTable->find()->where([
            'id' => 3,
            'active' => 0,
        ]);
        $this->assertEquals(1, $query->count());

        // PUT
        $this->put($url, []);

        // Confirm community has been reactivated
        $query = $communitiesTable->find()->where([
            'id' => 3,
            'active' => 1,
        ]);
        $this->assertEquals(1, $query->count());
    }
}
