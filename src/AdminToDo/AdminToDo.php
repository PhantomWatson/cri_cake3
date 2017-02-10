<?php
namespace App\AdminToDo;

use App\Model\Table\ProductsTable;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

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
    public $communitiesTable;
    public $productsTable;
    public $surveyTable;

    /**
     * AdminToDo constructor
     *
     * @return AdminToDo
     */
    public function __construct()
    {
        $this->communitiesTable = TableRegistry::get('Communities');
        $this->productsTable = TableRegistry::get('Products');
        $this->respondentsTable = TableRegistry::get('Respondents');
        $this->responsesTable = TableRegistry::get('Responses');
        $this->surveysTable = TableRegistry::get('Surveys');
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

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">assign a client</a>'
            ];
        }

        if ($this->waitingForOfficialsSurveyPurchase($communityId)) {
            $product = $this->productsTable->get(ProductsTable::OFFICIALS_SURVEY);

            return [
                'class' => 'waiting',
                'msg' => 'Waiting for client to purchase ' . $product->description
            ];
        }

        if ($this->readyToAdvanceToStepTwo($communityId)) {
            $url = Router::url([
                'prefix' => 'admin',
                'controller' => 'Communities',
                'action' => 'progress',
                $communityId
            ]);

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">advance to Step Two</a>'
            ];
        }

        if ($this->readyToCreateOfficialsSurvey($communityId)) {
            $url = Router::url([
                'prefix' => 'admin',
                'controller' => 'Surveys',
                'action' => 'link',
                $communityId,
                'official'
            ]);

            return [
                'class' => 'ready',
                'msg' => 'Ready to create and <a href="' . $url . '">link community-officials questionnaire</a>'
            ];
        }

        $officialsSurveyId = $this->surveysTable->getSurveyId($communityId, 'official');

        if ($this->readyToActivateSurvey($officialsSurveyId)) {
            $url = Router::url([
                'prefix' => 'admin',
                'controller' => 'Surveys',
                'action' => 'activate',
                $officialsSurveyId
            ]);

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">activate community-officials questionnaire</a>'
            ];
        }

        if ($this->waitingForSurveyInvitations($officialsSurveyId)) {
            return [
                'class' => 'waiting',
                'msg' => 'Waiting for client to send community officials questionnaire invitations'
            ];
        }

        if ($this->waitingForSurveyResponses($officialsSurveyId)) {
            return [
                'class' => 'waiting',
                'msg' => 'Waiting for responses to officials questionnaire'
            ];
        }

        if ($this->readyToConsiderDeactivating($officialsSurveyId)) {
            $url = Router::url([
                'prefix' => 'admin',
                'controller' => 'Surveys',
                'action' => 'activate',
                $officialsSurveyId
            ]);

            $approvedResponseCount = $this->responsesTable->getApprovedCount($officialsSurveyId);
            $invitationCount = $this->respondentsTable->getInvitedCount($officialsSurveyId);
            $mostRecentResponse = $this->responsesTable->find('all')
                ->select(['response_date'])
                ->where(['survey_id' => $officialsSurveyId])
                ->order(['response_date' => 'DESC'])
                ->first();
            $lastResponseDate = $mostRecentResponse->response_date->timeAgoInWords([
                'format' => 'MMM d, YYY',
                'end' => '+1 year'
            ]);

            $msg = 'Ready to consider <a href="' . $url . '">deactivating officials questionnaire</a>';
            $details = [
                '<li>Invitations: ' . $invitationCount . '</li>',
                '<li>Approved responses: ' . $approvedResponseCount . '</li>',
                '<li>Last response ' . $lastResponseDate . '</li>'
            ];
            $msg .= '<ul class="details">' . implode('', $details) . '</ul>';

            return [
                'class' => 'ready',
                'msg' => $msg
            ];
        }

        if ($this->readyToSchedulePresentation($communityId, 'a')) {
            $url = Router::url([
                'prefix' => 'admin',
                'controller' => 'Communities',
                'action' => 'presentations',
                $communityId
            ]);

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">schedule Presentation A</a>'
            ];
        }

        if ($this->readyToSchedulePresentation($communityId, 'b')) {
            $url = Router::url([
                'prefix' => 'admin',
                'controller' => 'Communities',
                'action' => 'presentations',
                $communityId
            ]);

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">schedule Presentation B</a>'
            ];
        }

        if ($this->readyToAdvanceToStepThree($communityId)) {
            $url = Router::url([
                'prefix' => 'admin',
                'controller' => 'Communities',
                'action' => 'progress',
                $communityId
            ]);

            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">advance to Step Three</a>'
            ];
        }

        return [
            'class' => 'incomplete',
            'msg' => '(criteria tests incomplete)'
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
                function ($exp, $q) use ($presentationLetter) {
                    return$exp->isNotNull("presentation_$presentationLetter");
                }
            ])
            ->count();

        // Already scheduled
        if ($count !== 0) {
            return false;
        }

        // Scheduling Presentation A is mandatory
        if ($presentationLetter == 'a') {
            return true;
        }

        // Optional Presentation B should only be scheduled if purchased
        if ($presentationLetter == 'b') {
            return $this->productsTable->isPurchased($communityId, ProductsTable::OFFICIALS_SUMMIT);
        }
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
}
