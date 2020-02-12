<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Client;

use App\Test\TestCase\ApplicationTest;

/**
 * App\Controller\PurchasesController Test Case
 *
 * @uses \App\Controller\Client\PurchasesController
 */
class PurchasesControllerTest extends ApplicationTest
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Areas',
        'app.Communities',
        'app.Products',
        'app.Purchases',
        'app.Respondents',
        'app.Responses',
        'app.Statistics',
        'app.Surveys',
        'app.Users',
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
