<?php
namespace App\Controller\Client;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

/**
 * Surveys Controller
 *
 * @property \App\Model\Table\SurveysTable $Surveys
 */
class SurveysController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('SurveyProcessing');
    }

    public function invite($respondentTypePlural = null)
    {
        // Find and validate community
        $clientId = $this->getClientId();
        $communitiesTable = TableRegistry::get('Communities');
        $communityId = $communitiesTable->getClientCommunityId($clientId);
        if (! $communityId || ! $communitiesTable->exists(['id' => $communityId])) {
            throw new NotFoundException('Sorry, we couldn\'t find the community corresponding with your account (#'.$clientId.')');
        }

        $this->Surveys->validateRespondentTypePlural($respondentTypePlural, $communityId);
        $respondentType = str_replace('s', '', $respondentTypePlural);
        $surveyId = $this->Surveys->getSurveyId($communityId, $respondentType);

        $respondentsTable = TableRegistry::get('Respondents');
        $approvedRespondents = $respondentsTable->getApprovedList($surveyId);
        $unaddressedUnapprovedRespondents = $respondentsTable->getUnaddressedUnapprovedList($surveyId);
        $allRespondents = array_merge($approvedRespondents, $unaddressedUnapprovedRespondents);

        if ($this->request->is('post')) {
            $params = compact(
                'allRespondents',
                'approvedRespondents',
                'communityId',
                'respondentType',
                'surveyId',
                'unaddressedUnapprovedRespondents'
            );
            $this->SurveyProcessing->processInvitations($params);
        }

        $titleForLayout = 'Invite Community '.ucwords($respondentTypePlural);
        $this->set(compact(
            'allRespondents',
            'approvedRespondents',
            'respondentTypePlural',
            'titleForLayout',
            'unaddressedUnapprovedRespondents'
        ));
    }
}
