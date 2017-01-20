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
        $this->_registry->getController()->paginate['sortWhitelist'] = ['approved', 'email', 'name'];
        $respondents = $this->_registry->getController()->paginate($query)->toArray();
        $this->_registry->getController()->set([
            'communityId' => $community->id,
            'respondents' => $respondents,
            'surveyType' => $surveyType,
            'titleForLayout' => $community->name . ' ' . ucwords($surveyType) . ' Questionnaire Respondents'
        ]);
    }
}
