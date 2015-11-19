<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class SurveysController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('SurveyProcessing');
    }

    public function index()
    {
        $communitiesTable = TableRegistry::get('Communities');
        $clientCommunities = $communitiesTable->getClientCommunityList();
        $clientCommunityIds = array_keys($clientCommunities);
        $this->paginate['Community'] = [
            'conditions' => ['id' => $clientCommunityIds],
            'contain' => [
                'OfficialSurvey' => [
                    'fields' => ['id']
                ],
                'OrganizationSurvey' => [
                    'fields' => ['id']
                ]
            ],
            'fields' => ['id', 'name', 'score']
        ];
        $this->set([
            'titleForLayout' => 'Surveys',
            'communities' => $this->paginate('Community')
        ]);
    }

    public function view($surveyId = null)
    {
        $survey = $this->Surveys->get($surveyId);
        $surveyStatus = $this->Surveys->getStatus($survey->community_id, $survey->type);

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($survey->community_id);

        /* Determines if this survey is currently being auto-imported
         * (because the community is in an appropriate stage of the CRI process) */
        $stageForAutoImport = $survey->type == 'official' ? 2 : 3;
        $isAutomaticallyImported = $community->score >= $stageForAutoImport && $community->score < ($stageForAutoImport + 1);

        $autoImportFrequency = $isAutomaticallyImported ? $this->Surveys->getPerSurveyAutoImportFrequency() : '';

        $respondentsTable = TableRegistry::get('Respondents');
        $this->set([
            'titleForLayout' => $community->name.' '.ucwords($survey->type).'s Survey',
            'isAdmin' => true,
            'isOpen' => $this->Surveys->isOpen($survey->community_id, $survey->type),
            'surveyUrl' => $survey->sm_url,
            'invitedRespondentCount' => $surveyStatus['invited_respondent_count'],
            'uninvitedRespondentCount' => $surveyStatus['uninvited_respondent_count'],
            'percentInvitedResponded' => $surveyStatus['percent_invited_responded'],
            'responsesChecked' => $surveyStatus['responses_checked'],
            'communityId' => $survey->community_id,
            'surveyType' => $survey->type,
            'surveyId' => $surveyId,
            'invitations' => $respondentsTable->getInvited($surveyId),
            'hasNewResponses' => $this->Surveys->newResponsesHaveBeenReceived($surveyId),
            'hasUninvitedUnaddressed' => $this->Surveys->hasUnaddressedUnapprovedRespondents($surveyId),
            'isAutomaticallyImported' => $isAutomaticallyImported,
            'autoImportFrequency' => $autoImportFrequency,
            'community' => $community,
            'stageForAutoImport' => $stageForAutoImport
        ]);
    }

    public function invite($surveyId = null)
    {
        $survey = $this->Surveys->get($surveyId);
        $communityId = $survey->community_id;
        $respondentType = $survey->type;
        $respondentsTable = TableRegistry::get('Respondents');
        $approvedRespondents = $respondentsTable->getApprovedList($surveyId);
        $unaddressedUnapprovedRespondents = $respondentsTable->getUnaddressedUnapprovedList($surveyId);
        $allRespondents = array_merge($approvedRespondents, $unaddressedUnapprovedRespondents);

        if ($this->request->is('post')) {
            $this->SurveyProcessing->processInvitations(compact(
                'allRespondents',
                'approvedRespondents',
                'communityId',
                'respondentType',
                'surveyId',
                'unaddressedUnapprovedRespondents'
            ));
        }

        // Looks dumb, but this is because it's the parameter for client_invite(), which shares a view
        $respondentTypePlural = $respondentType.'s';

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($survey->community_id);
        $titleForLayout = $community->name.': Invite Community '.$respondentTypePlural;

        $this->set(compact(
            'allRespondents',
            'approvedRespondents',
            'respondentTypePlural',
            'surveyId',
            'titleForLayout',
            'unaddressedUnapprovedRespondents'
        ));
        $this->render('..'.DS.'..'.DS.'Client'.DS.'Surveys'.DS.'invite');
    }
}
