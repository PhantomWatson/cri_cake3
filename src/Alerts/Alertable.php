<?php
namespace App\Alerts;

use App\Model\Entity\Community;
use App\Model\Table\CommunitiesTable;
use App\Model\Table\DeliverablesTable;
use App\Model\Table\DeliveriesTable;
use App\Model\Table\ProductsTable;
use App\Model\Table\SurveysTable;
use Cake\ORM\TableRegistry;

/**
 * Class Alertable
 *
 * Methods return a boolean values representing whether or not the selected communities qualify to receive the
 * specified alerts
 *
 * @package App\Alerts
 * @property CommunitiesTable $communities
 * @property Community $community
 * @property DeliveriesTable $deliveries
 * @property ProductsTable $products
 * @property SurveysTable $surveys
 */
class Alertable
{
    private $communities;
    private $community;
    private $deliveries;
    private $products;
    private $surveys;

    /**
     * Alertable constructor.
     *
     * @param int $communityId Community ID
     */
    public function __construct($communityId)
    {
        $this->communities = TableRegistry::get('Communities');
        $this->surveys = TableRegistry::get('Surveys');
        $this->deliveries = TableRegistry::get('Deliveries');
        $this->products = TableRegistry::get('Products');
        $this->community = $this->communities->get($communityId);
    }

    /**
     * Checks if the community is eligible to receive "deliver presentation A materials" alert
     *
     * Checks if
     * - Community is active
     * - Survey is inactive and has responses
     * - Presentation has not been delivered
     * - The corresponding product has been purchased
     *
     * @return bool
     */
    public function deliverPresentationA()
    {
        if (!$this->community->active) {
            return false;
        }

        $surveyType = 'official';
        $surveyId = $this->surveys->getSurveyId($this->community->id, $surveyType);
        $productId = ProductsTable::OFFICIALS_SURVEY;
        $deliverableId = DeliverablesTable::PRESENTATION_A_MATERIALS;

        return $this->deliverMandatoryPresentation($surveyId, $deliverableId, $productId);
    }

    /**
     * Checks if the community is eligible to receive "deliver presentation C materials" alert
     *
     * Checks if
     * - Community is active
     * - Survey is inactive and has responses
     * - Presentation has not been delivered
     * - The corresponding product has been purchased
     *
     * @return bool
     */
    public function deliverPresentationC()
    {
        if (!$this->community->active) {
            return false;
        }

        $surveyType = 'organization';
        $surveyId = $this->surveys->getSurveyId($this->community->id, $surveyType);
        $productId = ProductsTable::ORGANIZATIONS_SURVEY;
        $deliverableId = DeliverablesTable::PRESENTATION_C_MATERIALS;

        return $this->deliverMandatoryPresentation($surveyId, $deliverableId, $productId);
    }

    /**
     * Checks if the community is eligible to receive "deliver mandatory presentation materials" alert
     *
     * Checks if
     * - Survey is inactive and has responses
     * - Presentation has not been delivered
     * - The corresponding product has been purchased
     *
     * @param int $surveyId Survey ID
     * @param int $deliverableId Deliverable ID
     * @param int $productId Product ID
     * @return bool
     */
    private function deliverMandatoryPresentation($surveyId, $deliverableId, $productId)
    {
        if ($this->surveys->isActive($surveyId)) {
            return false;
        }

        if (!$this->surveys->hasResponses($surveyId)) {
            return false;
        }

        if ($this->deliveries->isRecorded($this->community->id, $deliverableId)) {
            return false;
        }

        if (!$this->products->isPurchased($this->community->id, $productId)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the community is eligible to receive "deliver presentation B materials" alert
     *
     * Checks if
     * - Community is active
     * - Presentation has not been delivered
     * - The corresponding product has been purchased
     *
     * @return bool
     */
    public function deliverPresentationB()
    {
        if (!$this->community->active) {
            return false;
        }

        $productId = ProductsTable::OFFICIALS_SUMMIT;
        $deliverableId = DeliverablesTable::PRESENTATION_B_MATERIALS;

        return $this->deliverOptionalPresentation($deliverableId, $productId);
    }

    /**
     * Checks if the community is eligible to receive "deliver presentation D materials" alert
     *
     * Checks if
     * - Community is active
     * - Presentation has not been delivered
     * - The corresponding product has been purchased
     *
     * @return bool
     */
    public function deliverPresentationD()
    {
        if (!$this->community->active) {
            return false;
        }

        $productId = ProductsTable::ORGANIZATIONS_SUMMIT;
        $deliverableId = DeliverablesTable::PRESENTATION_D_MATERIALS;

        return $this->deliverOptionalPresentation($deliverableId, $productId);
    }

    /**
     * Checks if the community is eligible to receive "deliver optional presentation materials" alert
     *
     * Checks if
     * - Presentation has not been delivered
     * - The corresponding product has been purchased
     *
     * @param int $deliverableId Deliverable ID
     * @param int $productId Product ID
     * @return bool
     */
    private function deliverOptionalPresentation($deliverableId, $productId)
    {
        if ($this->deliveries->isRecorded($this->community->id, $deliverableId)) {
            return false;
        }

        if (!$this->products->isPurchased($this->community->id, $productId)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the community is eligible to receive a "create officials survey" alert
     *
     * Checks if
     * - Survey does not exist
     * - The corresponding product has been purchased
     *
     * @return bool
     */
    public function createOfficialsSurvey()
    {
        return $this->createSurvey('official');
    }

    /**
     * Checks if the community is eligible to receive a "create officials survey" alert
     *
     * Checks if
     * - Survey does not exist
     * - The corresponding product has been purchased
     *
     * @return bool
     */
    public function createOrganizationsSurvey()
    {
        return $this->createSurvey('organization');
    }

    /**
     * Checks if the community is eligible to receive a "create survey" alert
     *
     * Checks if
     * - Survey does not exist
     * - The corresponding product has been purchased
     *
     * @param string $type Survey type (official or organization)
     * @return bool
     */
    private function createSurvey($type)
    {
        if ($this->surveys->hasBeenCreated($this->community->id, $type)) {
            return false;
        }

        $productId = $type == 'official' ? ProductsTable::OFFICIALS_SURVEY : ProductsTable::ORGANIZATIONS_SURVEY;
        if (!$this->products->isPurchased($this->community->id, $productId)) {
            return false;
        }

        return true;
    }
}
