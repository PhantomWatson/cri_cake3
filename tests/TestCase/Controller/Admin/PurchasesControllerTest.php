<?php
namespace App\Test\TestCase\Controller\Admin;

use App\Model\Table\PurchasesTable;
use App\Test\TestCase\ApplicationTest;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\PurchasesController Test Case
 * @property PurchasesTable $Purchases
 */
class PurchasesControllerTest extends ApplicationTest
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ActivityRecords',
        'app.Areas',
        'app.Communities',
        'app.Products',
        'app.Purchases',
        'app.QueuedJobs',
        'app.Respondents',
        'app.Responses',
        'app.Statistics',
        'app.Surveys',
        'app.Users'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Purchases') ? [] : ['className' => 'App\Model\Table\PurchasesTable'];
        $this->Purchases = TableRegistry::get('Purchases', $config);
        $this->configRequest([
            'environment' => ['HTTPS' => 'on']
        ]);
    }

    /**
     * Test for /admin/purchases/add
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAdd()
    {
        $communityId = 1;
        $productId = 2;

        // Ensure purchase has not yet been made
        $purchases = $this->Purchases->find()
            ->where([
                'community_id' => $communityId,
                'product_id' => $productId
            ])
            ->all();
        foreach ($purchases as $purchase) {
            $this->Purchases->delete($purchase);
        }

        // Fire off postback
        $url = [
            'prefix' => 'admin',
            'controller' => 'Purchases',
            'action' => 'add'
        ];
        $sources = array_keys($this->Purchases->getSourceOptions());
        $this->session($this->adminUser);
        $data = [
            'community_id' => $communityId,
            'product_id' => $productId,
            'source' => $sources[0],
            'notes' => ''
        ];
        $this->post($url, $data);

        // Confirm that purchase has been successfully made
        $this->assertRedirect();
        $expected = 1;
        $actual = $this->Purchases->find()
            ->where([
                'community_id' => $communityId,
                'product_id' => $productId
            ])
            ->count();
        $this->assertEquals($expected, $actual);
        $this->assertEventFired('Model.Purchase.afterAdminAdd', $this->_controller->getEventManager());
    }

    /**
     * Test for /admin/purchases/index
     *
     * @return void
     */
    public function testIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/purchases/ocra
     *
     * @return void
     */
    public function testOcra()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/purchases/refund
     *
     * @return void
     */
    public function testRefund()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/purchases/view
     *
     * @return void
     */
    public function testView()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
