<?php
namespace App\Test\TestCase\Controller;

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
     * Test postback method
     *
     * @return void
     */
    public function testPostback()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
