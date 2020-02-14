<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\PurchasesController Test Case
 *
 * @uses \App\Controller\PurchasesController
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
        'app.Communities',
        'app.Products',
        'app.Purchases',
        'app.QueuedJobs',
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
     * Test postback method
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testPostback()
    {
        // Purchase being made
        $userId = 1;
        $communityId = 1;
        $productId = 2;
        $productCode = 'EMC001-S483';

        // Confirm purchase has not yet been made
        $purchasesTable = TableRegistry::getTableLocator()->get('Purchases');
        $isPurchased = function () use ($purchasesTable, $userId, $communityId, $productId) {
            return (bool)$purchasesTable->find()
                ->where([
                    'user_id' => $userId,
                    'community_id' => $communityId,
                    'product_id' => $productId,
                ])
                ->count();
        };
        $this->assertFalse($isPurchased());

        // Fire off postback
        $url = [
            'controller' => 'Purchases',
            'action' => 'postback',
        ];
        $data = [
            'respmessage' => 'SUCCESS',
            'itemcode1' => $productCode,
            'custcode' => $userId,
            'ref1val1' => $communityId,
        ];
        $this->post($url, $data);

        // Confirm that purchase has been successfully made
        $this->assertResponseOk();
        $this->assertTrue($isPurchased());
        $this->assertEventFired('Model.Product.afterPurchase', $this->_controller->getEventManager());
    }
}
