<?php
namespace App\Test\TestCase\Controller;

use App\Controller\CommunitiesController;
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
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testAdd()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testEdit()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function testDelete()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
