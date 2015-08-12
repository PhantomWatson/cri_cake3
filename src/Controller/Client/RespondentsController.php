<?php
namespace App\Controller\Client;

use App\Controller\AppController;
use Cake\Network\Exception\BadRequestException;

class RespondentsController extends AppController
{
    private function setupPagination($communityId, $surveyType)
    {
        $surveysTable = TableRegistry::get('Surveys');
        $surveyId = $surveysTable->getSurveyId($communityId, $surveyType);
        $this->Paginator->settings = [
            'conditions' => ['Respondent.survey_id' => $surveyId],
            'contain' => [
                'Response' => [
                    'fields' => ['Response.response_date'],
                    'limit' => 1,
                    'order' => 'Response.response_date DESC'
                ]
            ],
            'fields' => [
                'Respondent.email',
                'Respondent.name',
                'Respondent.approved'
            ],
            'joins' => [
                [
                    'table' => 'responses',
                    'type' => 'left',
                    'alias' => 'Response',
                    'conditions' => ['Respondent.id = Response.respondent_id']
                ]
            ],
            'limit' => 50
        ];
    }

    public function index($surveyType = null)
    {
        if ($surveyType != 'official' && $surveyType != 'organization') {
            throw new BadRequestException('Survey type not specified');
        }

        $clientId = $this->getClientId();
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
}
