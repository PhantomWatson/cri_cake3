<?php
namespace App\AdminToDo;

use App\Model\Table\ActivityRecordsTable;
use App\Model\Table\CommunitiesTable;
use App\Model\Table\DeliverablesTable;
use App\Model\Table\DeliveriesTable;
use App\Model\Table\OptOutsTable;
use App\Model\Table\ProductsTable;
use App\Model\Table\PurchasesTable;
use App\Model\Table\RespondentsTable;
use App\Model\Table\ResponsesTable;
use App\Model\Table\SurveysTable;
use Cake\Chronos\Date;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use NumberFormatter;

/**
 * Handles the logic for the Admin To-Do page
 *
 * Each ready...() and waiting...() method assumes that none of the previous checks called before
 * it in getToDo() returned positive. In other words, these checks are meant to work in a chain,
 * but not in isolation.
 *
 * Class AdminToDo
 * @package App\AdminToDo
 */
class AdminToDo
{
    /** @var CommunitiesTable $communitiesTable */
    public $communitiesTable;

    /** @var OptOutsTable $optOutsTable */
    public $optOutsTable;

    /** @var ProductsTable $productsTable */
    public $productsTable;

    /** @var RespondentsTable $respondentsTable */
    public $respondentsTable;

    /** @var ResponsesTable $responsesTable */
    public $responsesTable;

    /** @var SurveysTable $surveysTable */
    public $surveysTable;

    /** @var DeliveriesTable $deliveriesTable */
    public $deliveriesTable;

    /** @var ActivityRecordsTable $activityRecordsTable */
    public $activityRecordsTable;

    /** @var PurchasesTable $purchasesTable */
    public $purchasesTable;

    /**
     * AdminToDo constructor
     */
    public function __construct()
    {
        $this->communitiesTable = TableRegistry::get('Communities');
        $this->optOutsTable = TableRegistry::get('OptOuts');
        $this->productsTable = TableRegistry::get('Products');
        $this->respondentsTable = TableRegistry::get('Respondents');
        $this->responsesTable = TableRegistry::get('Responses');
        $this->surveysTable = TableRegistry::get('Surveys');
        $this->deliveriesTable = TableRegistry::get('Deliveries');
        $this->activityRecordsTable = TableRegistry::get('ActivityRecords');
        $this->purchasesTable = TableRegistry::get('Purchases');
    }

    /**
     * Returns an array of ['class' => '...', 'msg' => '...']
     * representing the current to-do item for the selected community
     *
     * @param int $communityId Community ID
     * @return array
     */
    public function getToDo($communityId)
    {
        if ($this->readyForClientAssigned($communityId)) {
            $url = Router::url([
                'prefix' => 'admin',
                'controller' => 'Communities',
                'action' => 'clients',
                $communityId
            ]);

            $community = $this->communitiesTable->get($communityId);

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">assign a client</a>',
                'responsible' => ['ICI'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($community->created),
                    'since' => 'community added'
                ]
            ];
        }

        if ($this->optOutsTable->optedOut($communityId, ProductsTable::OFFICIALS_SURVEY)) {
            return [
                'class' => 'complete',
                'done' => true,
                'msg' => 'Opted out of further participation',
                'responsible' => null
            ];
        }

        $community = $this->communitiesTable->get($communityId);
        if ($this->waitingForOfficialsSurveyPurchase($communityId)) {
            $product = $this->productsTable->get(ProductsTable::OFFICIALS_SURVEY);

            return [
                'class' => 'waiting',
                'msg' => 'Waiting for client to purchase ' . $product->description,
                'responsible' => ['Client'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($community->created),
                    'since' => 'purchase'
                ]
            ];
        }

        if ($this->readyToAdvanceToStepTwo($communityId)) {
            $url = $this->getProgressUrl($communityId);
            $purchaseDate = $this->purchasesTable->getPurchaseDate(ProductsTable::OFFICIALS_SURVEY, $communityId);

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">advance to Step Two</a>',
                'responsible' => ['ICI'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($purchaseDate),
                    'since' => 'officials questionnaire purchased'
                ]
            ];
        }

        if ($this->readyToCreateOfficialsSurvey($communityId)) {
            $url = $this->getLinkUrl($community->slug, 'official');
            $promotionDate = $this->activityRecordsTable->getCommunityPromotionDate($communityId);

            return [
                'class' => 'ready',
                'msg' => 'Ready to create and <a href="' . $url . '">link officials questionnaire</a>',
                'responsible' => ['ICI'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($promotionDate),
                    'since' => 'community advanced to Step Two'
                ]
            ];
        }

        $officialsSurveyId = $this->surveysTable->getSurveyId($communityId, 'official');

        if ($this->readyToActivateSurvey($officialsSurveyId)) {
            $url = $this->getActivateUrl($officialsSurveyId);

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">activate officials questionnaire</a>',
                'responsible' => ['ICI']
            ];
        }

        $officialsSurvey = $this->surveysTable->get($officialsSurveyId);

        if ($this->waitingForSurveyInvitations($officialsSurveyId)) {
            $activationDate = $this->activityRecordsTable->getSurveyActivationDate($officialsSurveyId);
            $since = $activationDate ?: $officialsSurvey->created;
            $url = $this->getInvitationUrl($officialsSurveyId);

            return [
                'class' => 'waiting',
                'msg' => 'Waiting for client to <a href="' . $url . '">send officials questionnaire invitations</a>',
                'responsible' => ['Client'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($since),
                    'since' => 'questionnaire activated'
                ]
            ];
        }

        if ($this->waitingForSurveyResponses($officialsSurveyId)) {
            $invitationDate = $this->respondentsTable->getFirstInvitationDate($officialsSurveyId);
            $since = $invitationDate ?: $officialsSurvey->created;

            return [
                'class' => 'waiting',
                'msg' => 'Waiting for responses to officials questionnaire',
                'responsible' => null,
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($since),
                    'since' => $invitationDate ? 'first invitation sent' : 'questionnaire created'
                ]
            ];
        }

        if ($this->readyToConsiderDeactivating($officialsSurveyId)) {
            $lastResponseDate = $this->responsesTable->getMostRecentResponseDate($officialsSurveyId);

            return [
                'class' => 'ready',
                'msg' => $this->getDeactivationMsg($officialsSurveyId),
                'responsible' => ['CBER'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($lastResponseDate),
                    'since' => 'last response'
                ]
            ];
        }

        $deliverableId = DeliverablesTable::PRESENTATION_A_MATERIALS;
        if ($this->deliveryNotMade($communityId, $deliverableId)) {
            $url = $this->getDeliveryReportUrl($communityId, $deliverableId);
            $deactivationDate = $this->activityRecordsTable->getSurveyDeactivationDate($officialsSurveyId);

            return [
                'class' => 'ready',
                'msg' => 'Ready for CBER to <a href="' . $url . '">deliver Presentation A materials</a> to ICI',
                'responsible' => ['CBER'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($deactivationDate),
                    'since' => 'questionnaire deactivated'
                ]
            ];
        }

        if ($this->readyToSchedulePresentation($communityId, 'a')) {
            $url = $this->getPresentationsUrl($communityId);

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">schedule Presentation A</a>',
                'responsible' => ['ICI']
            ];
        }

        if ($this->waitingToCompletePresentation($communityId, 'a')) {
            return [
                'class' => 'waiting',
                'msg' => 'Waiting for Presentation A to complete ' .
                    '(' . $community->presentation_a->format('F j, Y') . ')',
                'responsible' => null
            ];
        }

        $purchased = $this->productsTable->isPurchased($communityId, ProductsTable::OFFICIALS_SUMMIT);
        $optedOut = $this->optOutsTable->optedOut($communityId, ProductsTable::OFFICIALS_SUMMIT);
        if ($purchased) {
            $deliverableId = DeliverablesTable::PRESENTATION_B_MATERIALS;
            if ($this->deliveryNotMade($communityId, $deliverableId)) {
                $url = $this->getDeliveryReportUrl($communityId, $deliverableId);
                $deactivationDate = $this->activityRecordsTable->getSurveyDeactivationDate($officialsSurveyId);
                $purchaseDate = $this->purchasesTable->getPurchaseDate(ProductsTable::OFFICIALS_SUMMIT, $communityId);
                if ($deactivationDate > $purchaseDate) {
                    $elapsed = [
                        'time' => $this->getWaitingPeriod($deactivationDate),
                        'since' => 'questionnaire deactivated'
                    ];
                } else {
                    $elapsed = [
                        'time' => $this->getWaitingPeriod($purchaseDate),
                        'since' => 'summit purchased'
                    ];
                }

                return [
                    'class' => 'ready',
                    'msg' => 'Ready for CBER to <a href="' . $url . '">deliver Presentation B materials</a> to ICI',
                    'responsible' => ['CBER'],
                    'elapsed' => $elapsed
                ];
            }

            if ($this->readyToSchedulePresentation($communityId, 'b')) {
                $url = $this->getPresentationsUrl($communityId);

                return [
                    'class' => 'ready',
                    'msg' => 'Ready to <a href="' . $url . '">schedule Presentation B</a>',
                    'responsible' => ['ICI']
                ];
            }

            if ($this->waitingToCompletePresentation($communityId, 'b')) {
                return [
                    'class' => 'waiting',
                    'msg' => 'Waiting for Presentation B to complete ' .
                        '(' . $community->presentation_b->format('F j, Y') . ')',
                    'responsible' => null
                ];
            }
        } elseif (! $optedOut) {
            $url = $this->getPresentationsUrl($communityId);

            return [
                'class' => 'waiting',
                'msg' => 'Waiting for client to purchase or <a href="' . $url . '">opt out of</a> Presentation B',
                'responsible' => ['Client'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($community->presentation_a),
                    'since' => 'Presentation A concluded'
                ]
            ];
        }

        $optedOut = $this->optOutsTable->optedOut($communityId, ProductsTable::ORGANIZATIONS_SURVEY);
        if ($optedOut) {
            return [
                'class' => 'complete',
                'done' => true,
                'msg' => 'Opted out of further participation',
                'responsible' => null
            ];
        }

        if ($this->waitingForOrganizationsSurveyPurchase($communityId)) {
            $product = $this->productsTable->get(ProductsTable::ORGANIZATIONS_SURVEY);
            $mostRecentPresentation = $community->presentation_b ?: $community->presentation_a;

            return [
                'class' => 'waiting',
                'msg' => 'Waiting for client to purchase or opt out of ' . $product->description,
                'responsible' => ['Client'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($mostRecentPresentation),
                    'since' => $community->presentation_b ? 'Presentation B concluded' : 'Presentation A concluded'
                ]
            ];
        }

        if ($this->readyToAdvanceToStepThree($communityId)) {
            $url = $this->getProgressUrl($communityId);

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">advance to Step Three</a>',
                'responsible' => ['ICI']
            ];
        }

        if ($this->readyToCreateOrganizationsSurvey($communityId)) {
            $url = $this->getLinkUrl($community->slug, 'organization');
            $promotionDate = $this->activityRecordsTable->getCommunityPromotionDate($communityId);

            return [
                'class' => 'ready',
                'msg' => 'Ready to create and <a href="' . $url . '">link organizations questionnaire</a>',
                'responsible' => ['ICI'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($promotionDate),
                    'since' => 'community advanced to Step Three'
                ]
            ];
        }

        $organizationsSurveyId = $this->surveysTable->getSurveyId($communityId, 'organization');

        if ($this->readyToActivateSurvey($organizationsSurveyId)) {
            $url = $this->getActivateUrl($organizationsSurveyId);

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">activate organizations questionnaire</a>',
                'responsible' => ['ICI']
            ];
        }

        $organizationsSurvey = $this->surveysTable->get($organizationsSurveyId);

        if ($this->waitingForSurveyInvitations($organizationsSurveyId)) {
            $activationDate = $this->activityRecordsTable->getSurveyActivationDate($organizationsSurveyId);
            $since = $activationDate ?: $organizationsSurvey->created;
            $url = $this->getInvitationUrl($organizationsSurveyId);

            return [
                'class' => 'waiting',
                'msg' => 'Waiting for client to ' .
                    '<a href="' . $url . '">send organizations questionnaire invitations</a>',
                'responsible' => ['Client'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($since),
                    'since' => 'questionnaire activated'
                ]
            ];
        }

        if ($this->waitingForSurveyResponses($organizationsSurveyId)) {
            $invitationDate = $this->respondentsTable->getFirstInvitationDate($organizationsSurveyId);
            $since = $invitationDate ?: $organizationsSurvey->created;

            return [
                'class' => 'waiting',
                'msg' => 'Waiting for responses to organizations questionnaire',
                'responsible' => null,
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($since),
                    'since' => $invitationDate ? 'first invitation sent' : 'questionnaire created'
                ]
            ];
        }

        if ($this->readyToConsiderDeactivating($organizationsSurveyId)) {
            $lastResponseDate = $this->responsesTable->getMostRecentResponseDate($organizationsSurveyId);

            return [
                'class' => 'ready',
                'msg' => $this->getDeactivationMsg($organizationsSurveyId),
                'responsible' => ['CBER'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($lastResponseDate),
                    'since' => 'last response'
                ]
            ];
        }

        $deliverableId = DeliverablesTable::PRESENTATION_C_MATERIALS;
        if ($this->deliveryNotMade($communityId, $deliverableId)) {
            $url = $this->getDeliveryReportUrl($communityId, $deliverableId);
            $deactivationDate = $this->activityRecordsTable->getSurveyDeactivationDate($organizationsSurveyId);

            return [
                'class' => 'ready',
                'msg' => 'Ready for CBER to <a href="' . $url . '">deliver Presentation C materials</a> to ICI',
                'responsible' => ['CBER'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($deactivationDate),
                    'since' => 'questionnaire deactivated'
                ]
            ];
        }

        if ($this->readyToSchedulePresentation($communityId, 'c')) {
            $url = $this->getPresentationsUrl($communityId);

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">schedule Presentation C</a>',
                'responsible' => ['ICI']
            ];
        }

        if ($this->waitingToCompletePresentation($communityId, 'c')) {
            return [
                'class' => 'waiting',
                'msg' => 'Waiting for Presentation C to complete ' .
                    '(' . $community->presentation_c->format('F j, Y') . ')',
                'responsible' => null
            ];
        }

        $purchased = $this->productsTable->isPurchased($communityId, ProductsTable::ORGANIZATIONS_SUMMIT);
        $optedOut = $this->optOutsTable->optedOut($communityId, ProductsTable::ORGANIZATIONS_SUMMIT);
        if ($purchased) {
            $deliverableId = DeliverablesTable::PRESENTATION_D_MATERIALS;
            if ($this->deliveryNotMade($communityId, $deliverableId)) {
                $url = $this->getDeliveryReportUrl($communityId, $deliverableId);
                $deactivationDate = $this->activityRecordsTable->getSurveyDeactivationDate($organizationsSurveyId);
                $purchaseDate = $this->purchasesTable->getPurchaseDate(ProductsTable::ORGANIZATIONS_SUMMIT, $communityId);
                if ($deactivationDate > $purchaseDate) {
                    $elapsed = [
                        'time' => $this->getWaitingPeriod($deactivationDate),
                        'since' => 'questionnaire deactivated'
                    ];
                } else {
                    $elapsed = [
                        'time' => $this->getWaitingPeriod($purchaseDate),
                        'since' => 'summit purchased'
                    ];
                }

                return [
                    'class' => 'ready',
                    'msg' => 'Ready for CBER to <a href="' . $url . '">deliver Presentation D materials</a> to ICI',
                    'responsible' => ['CBER'],
                    $elapsed
                ];
            }

            if ($this->readyToSchedulePresentation($communityId, 'd')) {
                $url = $this->getPresentationsUrl($communityId);

                return [
                    'class' => 'ready',
                    'msg' => 'Ready to <a href="' . $url . '">schedule Presentation D</a>',
                    'responsible' => ['ICI'],
                    'elapsed' => [
                        'time' => $this->getWaitingPeriod($community->presentation_c),
                        'since' => 'Presentation C concluded'
                    ]
                ];
            }

            if ($this->waitingToCompletePresentation($communityId, 'd')) {
                return [
                    'class' => 'waiting',
                    'msg' => 'Waiting for Presentation D to complete ' .
                        '(' . $community->presentation_d->format('F j, Y') . ')',
                    'responsible' => null
                ];
            }
        } elseif (! $optedOut) {
            return [
                'class' => 'waiting',
                'msg' => 'Waiting for client to purchase or opt out of Presentation D',
                'responsible' => ['Client'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($community->presentation_c),
                    'since' => 'Presentation C concluded'
                ]
            ];
        }

        if ($this->optOutsTable->optedOut($communityId, ProductsTable::POLICY_DEVELOPMENT)) {
            return [
                'class' => 'complete',
                'done' => true,
                'msg' => 'Opted out of further participation',
                'responsible' => null
            ];
        }

        $policyDev = $this->productsTable->get(ProductsTable::POLICY_DEVELOPMENT);
        $policyDevProductName = str_replace('PWRRR', 'PWR<sup>3</sup>', $policyDev->description);

        if ($this->waitingForPolicyDevPurchase($communityId)) {
            $mostRecentPresentation = $community->presentation_d ?: $community->presentation_c;

            return [
                'class' => 'waiting',
                'msg' => 'Waiting for client to purchase ' . $policyDevProductName,
                'responsible' => ['Client'],
                'elapsed' => [
                    'time' => $this->getWaitingPeriod($mostRecentPresentation),
                    'since' => $community->presentation_d ? 'Presentation D concluded' : 'Presentation C concluded'
                ]
            ];
        }

        if ($this->readyToAdvanceToStepFour($communityId)) {
            $url = $this->getProgressUrl($communityId);

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">advance to Step Four</a>',
                'responsible' => ['ICI']
            ];
        }

        $deliverableId = DeliverablesTable::POLICY_DEVELOPMENT;
        if ($this->deliveryNotMade($communityId, $deliverableId)) {
            $url = $this->getDeliveryReportUrl($communityId, $deliverableId);

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">deliver policy development</a>',
                'responsible' => [
                    'CBER',
                    'ICI'
                ]
            ];
        }

        return [
            'class' => 'complete',
            'done' => true,
            'msg' => 'Complete',
            'responsible' => null
        ];
    }

    /**
     * Returns whether or not the community needs a client assigned
     *
     * @param int $communityId Community ID
     * @return bool
     */
    private function readyForClientAssigned($communityId)
    {
        $clients = $this->communitiesTable->getClients($communityId);

        return empty($clients);
    }

    /**
     * Returns whether or not the community needs to purchase the Step Two survey
     *
     * @param int $communityId Community ID
     * @return bool
     */
    private function waitingForOfficialsSurveyPurchase($communityId)
    {
        $productId = ProductsTable::OFFICIALS_SURVEY;

        return ! $this->productsTable->isPurchased($communityId, $productId);
    }

    /**
     * Returns whether or not the community is ready to advance to Step Two
     *
     * @param int $communityId Community ID
     * @return bool
     */
    private function readyToAdvanceToStepTwo($communityId)
    {
        $community = $this->communitiesTable->get($communityId);

        return $community->score < 2;
    }

    /**
     * Returns whether or not the community needs an officials survey created
     *
     * @param int $communityId Community ID
     * @return bool
     */
    private function readyToCreateOfficialsSurvey($communityId)
    {
        return ! $this->surveysTable->hasBeenCreated($communityId, 'official');
    }

    /**
     * Returns whether or not the community needs this survey activated
     *
     * @param int $surveyId Survey ID
     * @return bool
     */
    private function readyToActivateSurvey($surveyId)
    {
        $hasResponses = $this->surveysTable->hasResponses($surveyId);
        $isActive = $this->surveysTable->isActive($surveyId);

        return ! $hasResponses && ! $isActive;
    }

    /**
     * Returns whether or not the community needs to send its first invitations for this survey
     *
     * @param int $surveyId Survey ID
     * @return bool
     */
    private function waitingForSurveyInvitations($surveyId)
    {
        return ! $this->surveysTable->hasSentInvitations($surveyId);
    }

    /**
     * Returns whether or not the community is waiting for responses
     *
     * @param int $surveyId Survey ID
     * @return bool
     */
    private function waitingForSurveyResponses($surveyId)
    {
        return ! $this->surveysTable->hasResponses($surveyId);
    }

    /**
     * Returns whether or not the community might qualify for survey deactivation
     *
     * @param int $surveyId Survey ID
     * @return bool
     */
    private function readyToConsiderDeactivating($surveyId)
    {
        return $this->surveysTable->isActive($surveyId);
    }

    /**
     * Returns a detailed message for "waiting for deactivation criteria to be met"
     *
     * @param int $surveyId Survey ID
     * @return string
     */
    private function getDeactivationMsg($surveyId)
    {
        $url = $this->getActivateUrl($surveyId);
        $approvedResponseCount = $this->responsesTable->getApprovedCount($surveyId);
        $invitationCount = $this->respondentsTable->getInvitedCount($surveyId);
        $msg = 'Ready to consider <a href="' . $url . '">deactivating officials questionnaire</a>';
        $details = [
            '<li>Invitations: ' . $invitationCount . '</li>',
            '<li>Approved responses: ' . $approvedResponseCount . '</li>',
        ];
        $msg .= '<ul class="details">' . implode('', $details) . '</ul>';

        return $msg;
    }

    /**
     * Returns whether or not the community needs to schedule the specified presentation
     *
     * @param int $communityId Community ID
     * @param string $presentationLetter a, b, c, or d
     * @return bool
     */
    private function readyToSchedulePresentation($communityId, $presentationLetter)
    {
        $count = $this->communitiesTable->find('all')
            ->where([
                'id' => $communityId,
                function ($exp) use ($presentationLetter) {
                    /** @var QueryExpression $exp */

                    return $exp->isNotNull("presentation_$presentationLetter");
                }
            ])
            ->count();

        // Already scheduled
        if ($count !== 0) {
            return false;
        }

        // Scheduling Presentations A and C is mandatory
        if (in_array($presentationLetter, ['a', 'c'])) {
            return true;
        }

        // Optional Presentations B and D should only be scheduled if purchased
        if ($presentationLetter == 'b') {
            return $this->productsTable->isPurchased($communityId, ProductsTable::OFFICIALS_SUMMIT);
        }
        if ($presentationLetter == 'd') {
            return $this->productsTable->isPurchased($communityId, ProductsTable::ORGANIZATIONS_SUMMIT);
        }

        return null;
    }

    /**
     * Returns whether or not the community needs to purchase the Step Three survey
     *
     * @param int $communityId Community ID
     * @return bool
     */
    private function waitingForOrganizationsSurveyPurchase($communityId)
    {
        return ! $this->productsTable->isPurchased($communityId, ProductsTable::ORGANIZATIONS_SURVEY);
    }

    /**
     * Returns whether or not the community is ready to advance to Step Three
     *
     * @param int $communityId Community ID
     * @return bool
     */
    private function readyToAdvanceToStepThree($communityId)
    {
        $purchaseMade = $this->productsTable->isPurchased($communityId, ProductsTable::ORGANIZATIONS_SURVEY);
        $community = $this->communitiesTable->get($communityId);
        $notYetAdvanced = $community->score < 3;

        return $purchaseMade && $notYetAdvanced;
    }

    /**
     * Returns whether or not the community needs an organizations survey created
     *
     * @param int $communityId Community ID
     * @return bool
     */
    private function readyToCreateOrganizationsSurvey($communityId)
    {
        return ! $this->surveysTable->hasBeenCreated($communityId, 'organization');
    }

    /**
     * Returns the URL for this community's 'update presentations' page
     *
     * @param int $communityId Community ID
     * @return string
     */
    private function getPresentationsUrl($communityId)
    {
        return Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'presentations',
            $communityId
        ]);
    }

    /**
     * Returns the URL for this community's 'progress' page
     *
     * @param int $communityId Community ID
     * @return string
     */
    private function getProgressUrl($communityId)
    {
        return Router::url([
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'progress',
            $communityId
        ]);
    }

    /**
     * Returns the URL for this survey's (de)activation page
     *
     * @param int $surveyId Survey ID
     * @return string
     */
    private function getActivateUrl($surveyId)
    {
        return Router::url([
            'prefix' => 'admin',
            'controller' => 'Surveys',
            'action' => 'activate',
            $surveyId
        ]);
    }

    /**
     * Returns the URL for this community / survey's 'link survey' page
     *
     * @param string $communitySlug Community slug
     * @param string $surveyType Survey type
     * @return string
     */
    private function getLinkUrl($communitySlug, $surveyType)
    {
        return Router::url([
            'prefix' => 'admin',
            'controller' => 'Surveys',
            'action' => 'link',
            $communitySlug,
            $surveyType
        ]);
    }

    /**
     * Returns the URL for this survey's invitation page
     *
     * @param int $surveyId Survey ID
     * @return string
     */
    private function getInvitationUrl($surveyId)
    {
        return Router::url([
            'prefix' => 'admin',
            'controller' => 'Surveys',
            'action' => 'invite',
            $surveyId
        ]);
    }

    /**
     * Returns whether or not the community needs to purchase policy development
     *
     * @param int $communityId Community ID
     * @return bool
     */
    private function waitingForPolicyDevPurchase($communityId)
    {
        return ! $this->productsTable->isPurchased($communityId, ProductsTable::POLICY_DEVELOPMENT);
    }

    /**
     * Returns whether or not the community is ready to advance to Step Four
     *
     * @param int $communityId Community ID
     * @return bool
     */
    private function readyToAdvanceToStepFour($communityId)
    {
        $community = $this->communitiesTable->get($communityId);

        return $community->score < 4;
    }

    /**
     * Returns whether or not a scheduled presentation has not yet concluded
     *
     * @param int $communityId Community ID
     * @param string $presentationLetter a, b, c, or d
     * @return bool
     */
    private function waitingToCompletePresentation($communityId, $presentationLetter)
    {
        $community = $this->communitiesTable->get($communityId);

        /** @var Date $presentationDate */
        $presentationDate = $community->{"presentation_$presentationLetter"};
        $today = date('Y-m-d');

        return $presentationDate && $presentationDate->format('Y-m-d') > $today;
    }

    /**
     * Returns a string like "more than a year", "five months", "12 days", or "less than a minute", depending
     * on how much time has passed since $time
     *
     * @param Time|FrozenTime|FrozenDate|null $time Time object
     * @return string
     */
    private function getWaitingPeriod($time)
    {
        if (! $time) {
            return 'unknown time';
        }

        $now = time();
        $waitingSince = $time->toUnixString();
        $waitingFor = $now - $waitingSince;

        $minute = 60;
        $hour = $minute * 60;
        $day = $hour * 24;
        $week = $day * 7;
        $month = $day * 30;
        $year = $day * 365;

        $formatWaitingPeriod = function ($count, $singular, $plural) {
            $f = new NumberFormatter('en', NumberFormatter::SPELLOUT);
            if ($count >= 10) {
                return "$count $plural";
            }

            return $f->format($count) . ' ' . __n($singular, $plural, $count);
        };

        if ($waitingFor > $year) {
            return 'more than a year';
        }

        if ($waitingFor >= $month) {
            $months = floor($waitingFor / $month);

            return $formatWaitingPeriod($months, 'month', 'months');
        }

        if ($waitingFor >= $week) {
            $weeks = floor($waitingFor / $week);

            return $formatWaitingPeriod($weeks, 'week', 'weeks');
        }

        if ($waitingFor >= $day) {
            $days = floor($waitingFor / $day);

            return $formatWaitingPeriod($days, 'day', 'days');
        }

        if ($waitingFor >= $hour) {
            $hours = floor($waitingFor / $hour);

            return $formatWaitingPeriod($hours, 'hour', 'hours');
        }

        if ($waitingFor >= $minute) {
            $hours = floor($waitingFor / $hour);

            return $formatWaitingPeriod($hours, 'hour', 'hours');
        }

        return 'less than a minute';
    }

    /**
     * Returns the URL for a "report delivery" page
     *
     * @param int $communityId Community ID
     * @param int $deliverableId Deliverable ID
     * @return string
     */
    private function getDeliveryReportUrl($communityId, $deliverableId)
    {
        return Router::url([
            'prefix' => 'admin',
            'controller' => 'Deliveries',
            'action' => 'add',
            $communityId,
            $deliverableId
        ]);
    }

    /**
     * Returns TRUE if the specified delivery has not been made
     *
     * @param int $communityId Community ID
     * @param int $deliverableId Deliverable ID
     * @return bool
     */
    private function deliveryNotMade($communityId, $deliverableId)
    {
        return ! $this->deliveriesTable->isRecorded($communityId, $deliverableId);
    }
}
