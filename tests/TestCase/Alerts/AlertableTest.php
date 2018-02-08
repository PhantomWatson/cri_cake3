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
     * Tests that the community does not qualify for the specified alert
     *
     * @param int $communityId Community ID to be checked
     * @param string $alertName An alert name, such as 'deliverPresentationA'
     * @return void
     */
    private function assertUnalertable($communityId, $alertName)
    {
        $alertable = new Alertable($communityId);
        $this->assertFalse($alertable->{$alertName}());
    }

    /**
     * Tests that the community qualifies for the specified alert
     *
     * @param int $communityId Community ID to be checked
     * @param string $alertName An alert name, such as 'deliverPresentationA'
     * @return void
     */
    private function assertAlertable($communityId, $alertName)
    {
        $alertable = new Alertable($communityId);
        $this->assertTrue($alertable->{$alertName}());
    }

    /**
     * Tests the "community is inactive" fail condition
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param string $presentationLetter A or C
     * @return void
     */
    private function _testDeliverPresFailInactiveCommunity($communityId, $presentationLetter)
    {
        $community = $this->communities->get($communityId);
        $community->active = false;
        $this->communities->save($community);
        $this->assertUnalertable($communityId, "deliverPresentation$presentationLetter");
    }

    /**
     * Tests the "survey is active" fail condition
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param int $surveyId Survey ID
     * @param string $presentationLetter A or C
     * @return void
     */
    private function _testDeliverPresFailActiveSurvey($communityId, $surveyId, $presentationLetter)
    {
        $survey = $this->surveys->get($surveyId);
        $survey->active = true;
        $this->surveys->save($survey);
        $this->assertUnalertable($communityId, "deliverPresentation$presentationLetter");
    }

    /**
     * Tests the "survey is active" fail condition
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param int $surveyId Survey ID
     * @param string $presentationLetter A or C
     * @return void
     */
    private function _testDeliverPresFailNoResponses($communityId, $surveyId, $presentationLetter)
    {
        // Survey with no responses
        $this->responses->deleteAll(['survey_id' => $surveyId]);
        $this->assertUnalertable($communityId, "deliverPresentation$presentationLetter");
    }

    /**
     * Tests "presentation has been delivered" fail condition
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param int $deliverableId Deliverable ID
     * @param string $presentationLetter A or C
     * @return void
     */
    private function _testDeliverPresFailDelivered($communityId, $deliverableId, $presentationLetter)
    {
        $delivery = $this->deliveries->newEntity([
            'deliverable_id' => $deliverableId,
            'user_id' => 1,
            'community_id' => $communityId,
        ]);
        $this->deliveries->save($delivery);
        $this->assertUnalertable($communityId, "deliverPresentation$presentationLetter");
    }

    /**
     * Tests "product has not been purchased" fail condition
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param string $presentationLetter A or C
     * @return void
     */
    private function _testDeliverPresFailNotPurchased($communityId, $presentationLetter)
    {
        $this->purchases->deleteAll(['community_id' => $communityId]);
        $this->assertUnalertable($communityId, "deliverPresentation$presentationLetter");
    }

    /**
     * Tests "presentation has been opted out of" fail condition
     *
     * @param int $communityId ID of an alertable community that will be manipulated to make un-alertable
     * @param int $productId Product ID
     * @param string $presentationLetter A or C
     * @return void
     */
    private function _testDeliverPresFailOptedOut($communityId, $productId, $presentationLetter)
    {
        $this->optOuts->addOptOut([
            'community_id' => $communityId,
            'product_id' => $productId,
            'user_id' => 1
        ]);
        $this->assertUnalertable($communityId, "deliverPresentation$presentationLetter");
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailInactiveCommunity()
    {
        $communityId = 4;
        $presentationLetter = 'A';
        $this->_testDeliverPresFailInactiveCommunity($communityId, $presentationLetter);
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
        $presentationLetter = 'A';
        $this->_testDeliverPresFailActiveSurvey($communityId, $surveyId, $presentationLetter);
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
        $presentationLetter = 'A';
        $this->_testDeliverPresFailNoResponses($communityId, $surveyId, $presentationLetter);
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
        $presentationLetter = 'A';
        $this->_testDeliverPresFailDelivered($communityId, $deliverableId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationA()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationAFailNotPurchased()
    {
        $communityId = 4;
        $presentationLetter = 'A';
        $this->_testDeliverPresFailNotPurchased($communityId, $presentationLetter);
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
        $presentationLetter = 'A';
        $this->_testDeliverPresFailOptedOut($communityId, $productId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationA()'s pass conditions:
     *
     * - Active community
     * - Survey is inactive and has responses
     * - Presentation has not been delivered
     * - The corresponding product has been purchased
     * - The presentation has not been opted out of
     *
     * @return void
     */
    public function testDeliverPresentationAPass()
    {
        $communityId = 4;
        $this->assertAlertable($communityId, 'deliverPresentationA');
    }

    /**
     * Tests Alertable::deliverPresentationC()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationCFailInactiveCommunity()
    {
        $communityId = 4;
        $presentationLetter = 'C';
        $this->_testDeliverPresFailInactiveCommunity($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationC()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationCFailActiveSurvey()
    {
        $communityId = 4;
        $surveyId = 5;
        $presentationLetter = 'C';
        $this->_testDeliverPresFailActiveSurvey($communityId, $surveyId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationC()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationCFailNoResponses()
    {
        $communityId = 4;
        $surveyId = 5;
        $presentationLetter = 'C';
        $this->_testDeliverPresFailNoResponses($communityId, $surveyId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationC()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationCFailDelivered()
    {
        $communityId = 4;
        $deliverableId = DeliverablesTable::PRESENTATION_C_MATERIALS;
        $presentationLetter = 'C';
        $this->_testDeliverPresFailDelivered($communityId, $deliverableId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationC()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationCFailNotPurchased()
    {
        $communityId = 4;
        $presentationLetter = 'C';
        $this->_testDeliverPresFailNotPurchased($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationC()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationCFailOptedOut()
    {
        $communityId = 4;
        $productId = ProductsTable::ORGANIZATIONS_SURVEY;
        $presentationLetter = 'C';
        $this->_testDeliverPresFailOptedOut($communityId, $productId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationC()'s pass conditions:
     *
     * - Active community
     * - Survey is inactive and has responses
     * - Presentation has not been delivered
     * - The corresponding product has been purchased
     * - The presentation has not been opted out of
     *
     * @return void
     */
    public function testDeliverPresentationCPass()
    {
        $communityId = 4;
        $this->assertAlertable($communityId, 'deliverPresentationC');
    }

    /**
     * Tests Alertable::deliverPresentationB()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationBFailInactiveCommunity()
    {
        $communityId = 4;
        $presentationLetter = 'B';
        $this->_testDeliverPresFailInactiveCommunity($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationB()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationBFailDelivered()
    {
        $communityId = 4;
        $deliverableId = DeliverablesTable::PRESENTATION_B_MATERIALS;
        $presentationLetter = 'B';
        $this->_testDeliverPresFailDelivered($communityId, $deliverableId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationB()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationBFailNotPurchased()
    {
        $communityId = 4;
        $presentationLetter = 'B';
        $this->_testDeliverPresFailNotPurchased($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationB()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationBFailOptedOut()
    {
        $communityId = 4;
        $productId = ProductsTable::OFFICIALS_SUMMIT;
        $presentationLetter = 'B';
        $this->_testDeliverPresFailOptedOut($communityId, $productId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationB()'s pass conditions:
     *
     * - Active community
     * - Survey is inactive and has responses
     * - Presentation has not been delivered
     * - The corresponding product has been purchased
     * - The presentation has not been opted out of
     *
     * @return void
     */
    public function testDeliverPresentationBPass()
    {
        $communityId = 4;
        $this->assertAlertable($communityId, 'deliverPresentationB');
    }

    /**
     * Tests Alertable::deliverPresentationD()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationDFailInactiveCommunity()
    {
        $communityId = 4;
        $presentationLetter = 'D';
        $this->_testDeliverPresFailInactiveCommunity($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationD()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationDFailDelivered()
    {
        $communityId = 4;
        $deliverableId = DeliverablesTable::PRESENTATION_D_MATERIALS;
        $presentationLetter = 'D';
        $this->_testDeliverPresFailDelivered($communityId, $deliverableId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationD()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationDFailNotPurchased()
    {
        $communityId = 4;
        $presentationLetter = 'D';
        $this->_testDeliverPresFailNotPurchased($communityId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationD()'s fail conditions
     *
     * @return void
     */
    public function testDeliverPresentationDFailOptedOut()
    {
        $communityId = 4;
        $productId = ProductsTable::ORGANIZATIONS_SUMMIT;
        $presentationLetter = 'D';
        $this->_testDeliverPresFailOptedOut($communityId, $productId, $presentationLetter);
    }

    /**
     * Tests Alertable::deliverPresentationD()'s pass conditions:
     *
     * - Active community
     * - Survey is inactive and has responses
     * - Presentation has not been delivered
     * - The corresponding product has been purchased
     * - The presentation has not been opted out of
     *
     * @return void
     */
    public function testDeliverPresentationDPass()
    {
        $communityId = 4;
        $this->assertAlertable($communityId, 'deliverPresentationD');
    }

    /**
     * Tests Alertable::createOfficialsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testCreateOfficialsSurveyFailInactiveCommunity()
    {
        $communityId = 5;
        $community = $this->communities->get($communityId);
        $community->active = false;
        $this->communities->save($community);
        $this->assertUnalertable($communityId, "createOfficialsSurvey");
    }

    /**
     * Tests Alertable::createOfficialsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testCreateOfficialsSurveyFailSurveyExists()
    {
        $communityId = 5;
        $survey = $this->surveys->get(1);
        $survey->community_id = $communityId;
        $this->surveys->save($survey);
        $this->assertUnalertable($communityId, "createOfficialsSurvey");
    }

    /**
     * Tests Alertable::createOfficialsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testCreateOfficialsSurveyFailNotPurchased()
    {
        $communityId = 5;
        $this->purchases->deleteAll(['community_id' => $communityId]);
        $this->assertUnalertable($communityId, "createOfficialsSurvey");
    }

    /**
     * Tests Alertable::createOfficialsSurvey()'s pass conditions:
     *
     * - Active community
     * - Survey does not exist
     * - The corresponding product has been purchased
     *
     * @return void
     */
    public function testCreateOfficialsSurveyPass()
    {
        $communityId = 5;
        $this->assertAlertable($communityId, 'createOfficialsSurvey');
    }

    /**
     * Tests Alertable::createOrganizationsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testCreateOrganizationsSurveyFailInactiveCommunity()
    {
        $communityId = 5;
        $community = $this->communities->get($communityId);
        $community->active = false;
        $this->communities->save($community);
        $this->assertUnalertable($communityId, "createOrganizationsSurvey");
    }

    /**
     * Tests Alertable::createOrganizationsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testCreateOrganizationsSurveyFailSurveyExists()
    {
        $communityId = 5;
        $survey = $this->surveys->get(1);
        $survey->community_id = $communityId;
        $this->surveys->save($survey);
        $this->assertUnalertable($communityId, "createOrganizationsSurvey");
    }

    /**
     * Tests Alertable::createOrganizationsSurvey()'s fail conditions
     *
     * @return void
     */
    public function testCreateOrganizationsSurveyFailNotPurchased()
    {
        $communityId = 5;
        $this->purchases->deleteAll(['community_id' => $communityId]);
        $this->assertUnalertable($communityId, "createOrganizationsSurvey");
    }

    /**
     * Tests Alertable::createOrganizationsSurvey()'s pass conditions:
     *
     * - Active community
     * - Survey does not exist
     * - The corresponding product has been purchased
     *
     * @return void
     */
    public function testCreateOrganizationsSurveyPass()
    {
        $communityId = 5;
        $this->assertAlertable($communityId, 'createOrganizationsSurvey');
    }
}
