<?php
namespace App\Controller\Client;

use App\Controller\AppController;
use Cake\Network\Exception\BadRequestException;
use Cake\ORM\TableRegistry;

class RespondentsController extends AppController
{
    private function setupPagination($communityId, $surveyType)
    {
        $surveysTable = TableRegistry::get('Surveys');
        $surveyId = $surveysTable->getSurveyId($communityId, $surveyType);
        $this->paginate = [
            'conditions' => ['Respondents.survey_id' => $surveyId],
            'contain' => [
                'Responses' => function ($q) {
                    return $q
                        ->select(['respondent_id', 'response_date'])
                        ->limit(1)
                        ->order(['Responses.response_date' => 'DESC']);
                }
            ],
            'fields' => [
                'Respondents.id',
                'Respondents.email',
                'Respondents.name',
                'Respondents.approved'
            ],
            'limit' => 50
        ];
    }

    private function checkClientAuthorization($respondentId, $clientId)
    {
        if (! $this->Respondents->exists(['id' => $respondentId])) {
            throw new NotFoundException('Sorry, that respondent (#'.$respondentId.') could not be found.');
        }
        $isAuthorized = $this->Respondents->clientCanApproveRespondent($clientId, $respondentId);
        if (! $isAuthorized) {
            throw new ForbiddenException('You are not authorized to approve that respondent');
        }
    }

    public function index($surveyType = null)
    {
        if ($surveyType != 'official' && $surveyType != 'organization') {
            throw new BadRequestException('Survey type not specified');
        }

        $clientId = $this->getClientId();
        if (! $clientId) {
            return $this->chooseClientToImpersonate();
        }
        $communitiesTable = TableRegistry::get('Communities');
        $communityId = $communitiesTable->getClientCommunityId($clientId);
        if ($communityId) {
            $community = $communitiesTable->get($communityId);
            $titleForLayout = $community->name.' '.ucwords($surveyType).' Survey Respondents';
            $this->setupPagination($community->id, $surveyType);
            $respondents = $this->paginate();
        } else {
            $titleForLayout = 'Survey Respondents';
            $respondents = [];
        }
        $this->set(compact(
            'titleForLayout',
            'respondents',
            'surveyType'
        ));
    }

    public function unapproved($surveyType = null)
    {
        if ($surveyType != 'official' && $surveyType != 'organization') {
            throw new NotFoundException('Invalid survey type');
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
            'titleForLayout' => $community->name.' Uninvited '.ucwords($surveyType).' Survey Respondents',
            'respondents' => [
                'unaddressed' => $this->Respondents->getUnaddressedUnapproved($surveyId),
                'dismissed' => $this->Respondents->getDismissed($surveyId)
            ]
        ]);
    }

    public function approveUninvited($respondentId)
    {
        $clientId = $this->getClientId();
        if (! $clientId) {
            return $this->chooseClientToImpersonate();
        }
        $this->checkClientAuthorization($respondentId, $clientId);
        $respondent = $this->Respondents->get($respondentId);
        $respondent->approved = 1;
        $this->set([
            'success' => (boolean) $this->Respondents->save($respondent)
        ]);
    }

    public function dismissUninvited($respondentId)
    {
        $clientId = $this->getClientId();
        if (! $clientId) {
            return $this->chooseClientToImpersonate();
        }
        $this->checkClientAuthorization($respondentId, $clientId);
        $respondent = $this->Respondents->get($respondentId);
        $respondent->approved = -1;
        $this->set([
            'success' => (boolean) $this->Respondents->save($respondent)
        ]);
    }
}
