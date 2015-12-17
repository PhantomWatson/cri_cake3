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

    public function view($communityId = null, $surveyType = null)
    {
        $surveyId = $this->Surveys->getSurveyId($communityId, $surveyType);
        $survey = $this->Surveys->get($surveyId);

        if ($this->request->is(['post', 'put'])) {
            $survey = $this->Surveys->patchEntity($survey, $this->request->data());
            $errors = $survey->errors();
            if (empty($errors) && $this->Surveys->save($survey)) {
                $message = $survey->isNew() ? 'Survey successfully linked to this community' : 'Survey details updated';
                $this->Flash->success($message);
            } else {
                $message = $survey->isNew() ? 'linking survey' : 'updating survey details';
                $this->Flash->error('There was an error '.$message.'. Please try again or contact an administrator for assistance.');
            }
        }

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
            'autoImportFrequency' => $autoImportFrequency,
            'community' => $community,
            'communityId' => $survey->community_id,
            'hasNewResponses' => $this->Surveys->newResponsesHaveBeenReceived($surveyId),
            'hasUninvitedUnaddressed' => $this->Surveys->hasUnaddressedUnapprovedRespondents($surveyId),
            'invitations' => $respondentsTable->getInvited($surveyId),
            'invitedRespondentCount' => $surveyStatus['invited_respondent_count'],
            'isAdmin' => true,
            'isAutomaticallyImported' => $isAutomaticallyImported,
            'isOpen' => $this->Surveys->isOpen($survey->community_id, $survey->type),
            'percentInvitedResponded' => $surveyStatus['percent_invited_responded'],
            'responsesChecked' => $surveyStatus['responses_checked'],
            'stageForAutoImport' => $stageForAutoImport,
            'survey' => $survey,
            'surveyId' => $surveyId,
            'surveyType' => $survey->type,
            'surveyUrl' => $survey->sm_url,
            'titleForLayout' => $community->name.' '.ucwords($survey->type).'s Survey',
            'uninvitedRespondentCount' => $surveyStatus['uninvited_respondent_count'],
            'qnaIdFields' => $this->Surveys->getQnaIdFieldNames()
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
