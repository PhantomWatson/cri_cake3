<?php
namespace App\Test\TestCase\Alerts;

use App\Alerts\Alertable;
use App\Model\Table\CommunitiesTable;
use App\Model\Table\DeliverablesTable;
use App\Model\Table\DeliveriesTable;
use App\Model\Table\OptOutsTable;
use App\Model\Table\ProductsTable;
use App\Model\Table\PurchasesTable;
use App\Model\Table\ResponsesTable;
use App\Model\Table\SurveysTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Class AlertableTest
 * @package App\Test\TestCase\Alerts
 * @property array $fixtures
 * @property CommunitiesTable $communities
 * @property DeliveriesTable $deliveries
 * @property OptOutsTable $optOuts
 * @property PurchasesTable $purchases
 * @property ResponsesTable $responses
 * @property SurveysTable $surveys
 */
class AlertableTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.communities',
        'app.deliverables',
        'app.deliveries',
        'app.opt_outs',
        'app.products',
        'app.purchases',
        'app.responses',
        'app.surveys',
        'app.users',
    ];
    private $communities;
    private $deliveries;
    private $purchases;
    private $responses;
    private $surveys;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->communities = TableRegistry::get('Communities');
        $this->deliveries = TableRegistry::get('Deliveries');
        $this->optOuts = TableRegistry::get('OptOuts');
        $this->purchases = TableRegistry::get('Purchases');
        $this->responses = TableRegistry::get('Responses');
        $this->surveys = TableRegistry::get('Surveys');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailInactiveCommunity()
    {
        $communityId = 4;
        $community = $this->communities->get($communityId);

        // Inactive community
        $community->active = false;
        $this->communities->save($community);
        $alertable = new Alertable($communityId);
        $this->assertFalse($alertable->deliverPresentationA());
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailActiveSurvey()
    {
        $communityId = 4;
        $surveyId = 4;
        $survey = $this->surveys->get($surveyId);

        // Active survey
        $survey->active = true;
        $this->surveys->save($survey);
        $alertable = new Alertable($communityId);
        $this->assertFalse($alertable->deliverPresentationA());
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailNoResponses()
    {
        $communityId = 4;
        $surveyId = 4;

        // Survey with no responses
        $this->responses->deleteAll(['survey_id' => $surveyId]);
        $alertable = new Alertable($communityId);
        $this->assertFalse($alertable->deliverPresentationA());
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailDelivered()
    {
        $communityId = 4;
        $deliverableId = DeliverablesTable::PRESENTATION_A_MATERIALS;

        // Presentation has been delivered
        $delivery = $this->deliveries->newEntity([
            'deliverable_id' => $deliverableId,
            'user_id' => 1,
            'community_id' => $communityId,
        ]);
        $this->deliveries->save($delivery);
        $alertable = new Alertable($communityId);
        $this->assertFalse($alertable->deliverPresentationA());
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailNotPurchased()
    {
        $communityId = 4;

        // Product has not been purchased
        $this->purchases->deleteAll(['community_id' => $communityId]);
        $alertable = new Alertable($communityId);
        $this->assertFalse($alertable->deliverPresentationA());
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailOptedOut()
    {
        $communityId = 4;
        $productId = ProductsTable::OFFICIALS_SURVEY;

        // Presentation has been opted out of
        $this->optOuts->addOptOut([
            'community_id' => $communityId,
            'product_id' => $productId,
            'user_id' => 1
        ]);
        $alertable = new Alertable($communityId);
        $this->assertFalse($alertable->deliverPresentationA());
    }

    /**
     * Tests Alertable::deliverPresentationA()'s pass condition
     *
     * @return void
     */
    public function testDeliverPresentationAPass()
    {
        /*
         * - Active community
         * - Survey is inactive and has responses
         * - Presentation has not been delivered
         * - The corresponding product has been purchased
         * - The presentation has not been opted out of
         */

        $communityId = 4;
        $alertable = new Alertable($communityId);
        $this->assertTrue($alertable->deliverPresentationA());
    }
}
