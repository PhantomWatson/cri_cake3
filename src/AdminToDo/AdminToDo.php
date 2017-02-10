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
        return ! $this->surveysTable->isActive($surveyId);
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
}
