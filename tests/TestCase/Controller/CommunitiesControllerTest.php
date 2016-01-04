<?php
namespace App\Test\TestCase\Controller;

use App\Controller\CommunitiesController;
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
        'app.statistics',
        'app.surveys',
        'app.users'
    ];

    public function testIndex()
    {
        $this->get('/communities');
        $this->assertResponseOk();
    }

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

    public function testAutocomplete()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
