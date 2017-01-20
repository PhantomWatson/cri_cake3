<?php
namespace App\Controller\Client;

use App\Controller\AppController;
use App\Model\Entity\Respondent;
use Cake\Event\Event;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;

class RespondentsController extends AppController
{
    /**
     * Throws exceptions if the specified client cannot approve the specified respondent
     *
     * @param int $respondentId Respondent ID
     * @param int $clientId Client ID
     * @throws \App\Controller\Client\NotFoundException
     * @return void
     */
    private function checkClientAuthorization($respondentId, $clientId)
    {
        if (! $this->Respondents->exists(['id' => $respondentId])) {
            throw new NotFoundException('Sorry, that respondent (#' . $respondentId . ') could not be found.');
        }
        $isAuthorized = $this->Respondents->clientCanApproveRespondent($clientId, $respondentId);
        if (! $isAuthorized) {
            throw new ForbiddenException('You are not authorized to approve that respondent');
        }
    }

    /**
     * Index method
     *
     * @param string|null $surveyType Survey type
     * @return void
     */
    public function index($surveyType = null)
    {
        if ($surveyType != 'official' && $surveyType != 'organization') {
            throw new BadRequestException('Questionnaire type not specified');
        }

        $clientId = $this->Auth->user('id');
        $communitiesTable = TableRegistry::get('Communities');
        $communityId = $communitiesTable->getClientCommunityId($clientId);

        if ($communityId) {
            $this->loadComponent('SurveyResults');
            $this->SurveyResults->prepareRespondentsClientsPage(compact('communityId', 'surveyType'));
        } else {
            $this->set([
                'titleForLayout' => 'Questionnaire Respondents',
                'respondents' => [],
                'surveyType' => $surveyType
            ]);
        }
    }

    /**
     * Unapproved method
     *
     * @param string|null $surveyType Survey type
     * @return \App\Controller\Response
     * @throws \App\Controller\Client\NotFoundException
     */
    public function unapproved($surveyType = null)
    {
        if ($surveyType != 'official' && $surveyType != 'organization') {
            throw new NotFoundException('Invalid questionnaire type');
        }

        $communitiesTable = TableRegistry::get('Communities');
        $clientId = $this->getClientId();
        if (! $clientId) {
            return $this->chooseClientToImpersonate();
        }
        $communityId = $communitiesTable->getClientCommunityId($clientId);

        if (! $communityId) {
            throw new NotFoundException('Your account is not currently assigned to a community');
        }

        $community = $communitiesTable->get($communityId);
        $surveysTable = TableRegistry::get('Surveys');
        $surveyId = $surveysTable->getSurveyId($communityId, $surveyType);

        $this->set([
            'communityId' => $communityId,
            'respondents' => [
                'unaddressed' => $this->Respondents->getUnaddressedUnapproved($surveyId),
                'dismissed' => $this->Respondents->getDismissed($surveyId)
            ],
            'surveyType' => $surveyType,
            'titleForLayout' => $community->name . ' Uninvited ' . ucwords($surveyType) . ' Questionnaire Respondents'
        ]);
    }

    /**
     * ApproveUninvited method
     *
     * @param int $respondentId Respondent ID
     * @return \App\Controller\Response
     * @throws \App\Controller\Client\NotFoundException
     */
    public function approveUninvited($respondentId)
    {
        $clientId = $this->getClientId();
        if (! $clientId) {
            return $this->chooseClientToImpersonate();
        }
        $this->checkClientAuthorization($respondentId, $clientId);
        $respondent = $this->Respondents->get($respondentId);
        $respondent->approved = 1;
        $result = $this->Respondents->save($respondent);
        if ($result) {
            $this->dispatchUninvitedEvent(true, $respondent);
        }
        $this->set([
            'success' => (bool)$result
        ]);
        $this->viewBuilder()->layout('blank');
    }

    /**
     * DismissUninvited method
     *
     * @param int $respondentId Respondent ID
     * @return \App\Controller\Response
     * @throws \App\Controller\Client\NotFoundException
     */
    public function dismissUninvited($respondentId)
    {
        $clientId = $this->getClientId();
        if (! $clientId) {
            return $this->chooseClientToImpersonate();
        }
        $this->checkClientAuthorization($respondentId, $clientId);
        $respondent = $this->Respondents->get($respondentId);
        $respondent->approved = -1;
        $result = $this->Respondents->save($respondent);
        if ($result) {
            $this->dispatchUninvitedEvent(false, $respondent);
        }
        $this->set([
            'success' => (bool)$result
        ]);
        $this->viewBuilder()->layout('blank');
    }

    /**
     * Dispatches an event for uninvited respondent approval or dismissal
     *
     * @param bool $approved True for approved and false for dismissed
     * @param Respondent $respondent Respondent entity
     * @return void
     */
    private function dispatchUninvitedEvent($approved, $respondent)
    {
        $surveyId = $respondent->survey_id;
        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($surveyId);
        $eventName = 'Model.Respondent.afterUninvited' . ($approved ? 'Approve' : 'Dismiss');
        $event = new Event($eventName, $this, ['meta' => [
            'communityId' => $survey->community_id,
            'surveyId' => $surveyId,
            'respondentId' => $respondent->id,
            'respondentName' => $respondent->name,
            'surveyType' => $survey->type
        ]]);
        $this->eventManager()->dispatch($event);
    }
}
