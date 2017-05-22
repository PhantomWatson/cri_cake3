<?php
namespace App\Test\TestCase\Controller\Client;

use App\Test\TestCase\ApplicationTest;

/**
 * App\Controller\PurchasesController Test Case
 */
class PurchasesControllerTest extends ApplicationTest
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

    /**
     * Test for /client/purchases/index
     *
     * @return void
     */
    public function testIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
