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
     * Test postback method
     *
     * @return void
     */
    public function testPostback()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/purchases/add
     *
     * @return void
     */
    public function testAdminAdd()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/purchases/index
     *
     * @return void
     */
    public function testAdminIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/purchases/ocra
     *
     * @return void
     */
    public function testAdminOcra()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/purchases/refund
     *
     * @return void
     */
    public function testAdminRefund()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/purchases/view
     *
     * @return void
     */
    public function testAdminView()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /client/purchases/index
     *
     * @return void
     */
    public function testClientIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
