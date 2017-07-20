<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;

class SurveyResultsComponent extends Component
{
    public $components = ['Flash', 'Auth'];

    /**
     * Prepares /admin/respondents/view or /client/respondents/index
     *
     * @param array $params Parameters
     * @return void
     */
    public function prepareRespondentsClientsPage($params)
    {
        $surveysTable = TableRegistry::get('Surveys');
        $communitiesTable = TableRegistry::get('Communities');
        if (isset($params['surveyId'])) {
            $surveyId = $params['surveyId'];
            $survey = $surveysTable->get($surveyId);
            $surveyType = $survey->type;
            $community = $communitiesTable->get($survey->community_id);
        } else {
            if (! isset($params['communityId']) || ! isset($params['surveyType'])) {
                throw new InternalErrorException('Error: Community ID and survey type must be specified');
            }
            $surveyType = $params['surveyType'];
            $community = $communitiesTable->get($params['communityId']);
            $surveyId = $surveysTable->getSurveyId($community->id, $params['surveyType']);
        }

        $respondentsTable = TableRegistry::get('Respondents');
        $query = $respondentsTable->find('all')
            ->select([
                'Respondents.id',
                'Respondents.email',
                'Respondents.name',
                'Respondents.title',
                'Respondents.invited',
                'Respondents.approved'
            ])
            ->where(['Respondents.survey_id' => $surveyId])
            ->contain([
                'Responses' => function ($q) {
                    return $q
                        ->select(['respondent_id', 'response_date'])
                        ->order(['Responses.response_date' => 'DESC']);
                }
            ])
            ->order(['name' => 'ASC']);

        $invitationCount = 0;
        $approvedResponseCount = 0;
        $mostRecentResponseDate = null;
        $respondents = $query->all();
        foreach ($respondents as $respondent) {
            if ($respondent->invited) {
                $invitationCount++;
            }
            if ($respondent->approved && ! empty($respondent->responses)) {
                $approvedResponseCount++;
            }
            if (! empty($respondent->responses)) {
                $date = $respondent->responses[0]['response_date']->format('Y-m-d');
                if ($date > $mostRecentResponseDate) {
                    $mostRecentResponseDate = $date;
                }
            }
        }
        if ($mostRecentResponseDate) {
            $mostRecentResponseDate = date('F j, Y', strtotime($mostRecentResponseDate));
        } else {
            $mostRecentResponseDate = 'N/A';
        }

        if ($invitationCount) {
            $responseRate = round(($approvedResponseCount / $invitationCount) * 100) . '%';
        } else {
            $responseRate = 'N/A';
        }

        $this->_registry->getController()->paginate['sortWhitelist'] = ['approved', 'email', 'name'];
        $paginatedRespondents = $this->_registry->getController()->paginate($query)->toArray();

        $this->_registry->getController()->set([
            'approvedResponseCount' => $approvedResponseCount,
            'communityId' => $community->id,
            'community' => $community,
            'invitationCount' => $invitationCount,
            'mostRecentResponseDate' => $mostRecentResponseDate,
            'respondents' => $paginatedRespondents,
            'responseRate' => $responseRate,
            'surveyType' => $surveyType,
            'titleForLayout' => $community->name . ' ' . ucwords($surveyType) . ' Questionnaire Respondents'
        ]);
    }
}
