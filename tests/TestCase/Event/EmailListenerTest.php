<?php
namespace App\Test\TestCase\Event;

use App\Event\EmailListener;
use App\Model\Table\ProductsTable;
use App\Test\TestCase\ApplicationTest;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

class EmailListenerTest extends ApplicationTest
{
    public $fixtures = [
        'app.clients_communities',
        'app.communities',
        'app.queued_jobs',
        'app.surveys',
        'app.users',
    ];

    /**
     * SetUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Tests EmailListener::sendDeliverMandatoryPresentationEmail()
     *
     * @return void
     * @throws \Exception
     */
    public function testSendMandPresEmail()
    {
        $listener = new EmailListener();
        $event = new Event('Model.Survey.afterDeactivate');
        $meta = ['communityId' => 1];
        $listener->sendDeliverMandatoryPresentationEmail($event, $meta);
        $this->assertAdminTaskEmailEnqueued('deliverMandatoryPresentation');
    }

    /**
     * Tests EmailListener::sendDeliverOptPresentationEmail()
     *
     * @return void
     * @throws \Exception
     */
    public function testSendOptPresEmail()
    {
        $listener = new EmailListener();
        $event = new Event('Model.Product.afterPurchase');
        $meta = [
            'communityId' => 1,
            'productId' => ProductsTable::OFFICIALS_SUMMIT
        ];
        $listener->sendDeliverOptPresentationEmail($event, $meta);
        $this->assertAdminTaskEmailEnqueued('deliverOptionalPresentation');
    }

    /**
     * Tests that EmailListener::implementedEvents() contains all required triggers
     *
     * @return void
     */
    public function testImplementedEvents()
    {
        $required = [
            'Model.Community.afterAutomaticAdvancement' => 'sendCommunityPromotedEmail',
            'Model.Community.afterScoreIncrease' => 'sendCommunityPromotedEmail',
            'Model.Survey.afterDeactivate' => 'sendDeliverMandatoryPresentationEmail',
            'Model.Product.afterPurchase' => 'sendDeliverOptPresentationEmail',
            'Model.Purchase.afterAdminAdd' => 'sendDeliverOptPresentationEmail',
            'Model.Delivery.afterAdd' => 'sendSchedulePresentationEmail'
        ];
        $listener = new EmailListener();
        $actual = $listener->implementedEvents();
        foreach ($required as $event => $method) {
            $this->assertArrayHasKey($event, $actual);
            $this->assertEquals($method, $actual[$event]);
        }
    }

    /**
     * Tests EmailListener::sendDeliverOptPresentationEmail()
     *
     * @return void
     * @throws \Exception
     */
    public function testSendCreateSurveyEmail()
    {
        // Create necessary condition
        $surveysTable = TableRegistry::get('Surveys');
        $communityId = 1;
        $surveys = $surveysTable->find()
            ->where([
                'community_id' => $communityId
            ]);
        foreach ($surveys as $survey) {
            $surveysTable->delete($survey);
        }

        // Test if email was enqueued
        $listener = new EmailListener();
        $event = new Event('Model.Community.afterScoreIncrease');
        $meta = [
            'communityId' => $communityId,
            'toStep' => 2
        ];
        $listener->sendCommunityPromotedEmail($event, $meta);
        $this->assertAdminTaskEmailEnqueued('createSurvey');
    }
}
