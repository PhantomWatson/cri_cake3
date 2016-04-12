<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Mailer\Mailer;
use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
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
        if (! in_array($surveyType, ['official', 'organization'])) {
            throw new NotFoundException("Unknown survey type: $surveyType");
        }

        $communitiesTable = TableRegistry::get('Communities');
        if (! $communitiesTable->exists(['id' => $communityId])) {
            throw new NotFoundException("Community with ID $communityId not found");
        }

        $surveyId = $this->Surveys->getSurveyId($communityId, $surveyType);

        if ($surveyId) {
            $survey = $this->Surveys->get($surveyId);
        } else {
            return $this->redirect([
                'action' => 'link',
                $communityId,
                $surveyType
            ]);
        }

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($communityId);

        if ($survey->get('id')) {
            $this->prepareSurveyStatus($survey, $community);
        }

        $this->set([
            'community' => $community,
            'survey' => $survey,
            'titleForLayout' => $community->name.' '.ucwords($surveyType).'s Survey'
        ]);
    }

    public function link($communityId = null, $surveyType = null)
    {
        if (! in_array($surveyType, ['official', 'organization'])) {
            throw new NotFoundException("Unknown survey type: $surveyType");
        }

        $communitiesTable = TableRegistry::get('Communities');
        if (! $communitiesTable->exists(['id' => $communityId])) {
            throw new NotFoundException("Community with ID $communityId not found");
        }

        $surveyId = $this->Surveys->getSurveyId($communityId, $surveyType);

        if ($surveyId) {
            $survey = $this->Surveys->get($surveyId);
        } else {
            $survey = $this->Surveys->newEntity();
            $survey->community_id = $communityId;
            $survey->type = $surveyType;
        }

        if ($this->request->is(['post', 'put'])) {
            $survey = $this->Surveys->patchEntity($survey, $this->request->data());
            $errors = $survey->errors();
            $isNew = $survey->isNew();
            if (empty($errors) && $this->Surveys->save($survey)) {
                $message = $isNew ? 'Survey successfully linked to this community' : 'Survey details updated';
                $this->Flash->success($message);
                $this->redirect([
                    'action' => 'view',
                    $communityId,
                    $surveyType
                ]);
            } else {
                $message = $survey->isNew() ? 'linking survey' : 'updating survey details';
                $this->Flash->error('There was an error '.$message.'. Please try again or contact an administrator for assistance.');
            }
        }

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($communityId);

        $this->set([
            'community' => $community,
            'qnaIdFields' => $this->Surveys->getQnaIdFieldNames(),
            'survey' => $survey,
            'titleForLayout' => $community->name.' '.ucwords($surveyType).'s Survey: Link'
        ]);
    }

    private function prepareSurveyStatus($survey, $community)
    {
        $surveyStatus = $this->Surveys->getStatus($survey->community_id, $survey->type);

        /* Determines if this survey is currently being auto-imported
         * (because the community is in an appropriate stage of the CRI process) */
        $stageForAutoImport = $survey->type == 'official' ? 2 : 3;
        $isAutomaticallyImported = $community->score >= $stageForAutoImport && $community->score < ($stageForAutoImport + 1);

        $autoImportFrequency = $isAutomaticallyImported ? $this->Surveys->getPerSurveyAutoImportFrequency() : '';

        $respondentsTable = TableRegistry::get('Respondents');
        $this->set([
            'autoImportFrequency' => $autoImportFrequency,
            'hasNewResponses' => $this->Surveys->newResponsesHaveBeenReceived($survey->id),
            'hasUninvitedUnaddressed' => $this->Surveys->hasUnaddressedUnapprovedRespondents($survey->id),
            'invitations' => $respondentsTable->getInvited($survey->id),
            'invitedRespondentCount' => $surveyStatus['invited_respondent_count'],
            'isAutomaticallyImported' => $isAutomaticallyImported,
            'isOpen' => $this->Surveys->isOpen($survey->community_id, $survey->type),
            'percentInvitedResponded' => $surveyStatus['percent_invited_responded'],
            'responsesChecked' => $surveyStatus['responses_checked'],
            'stageForAutoImport' => $stageForAutoImport,
            'uninvitedRespondentCount' => $surveyStatus['uninvited_respondent_count']
        ]);
    }

    public function invite($surveyId = null)
    {
        $survey = $this->Surveys->get($surveyId);
        $communityId = $survey->community_id;
        $respondentType = $survey->type;

        if ($this->request->is('post')) {
            $this->SurveyProcessing->processInvitations($communityId, $respondentType, $surveyId);
        }

        $respondentsTable = TableRegistry::get('Respondents');
        $approvedRespondents = $respondentsTable->getApprovedList($surveyId);
        $unaddressedUnapprovedRespondents = $respondentsTable->getUnaddressedUnapprovedList($surveyId);
        $allRespondents = array_merge($approvedRespondents, $unaddressedUnapprovedRespondents);

        // Looks dumb, but this is because it's the parameter for client_invite(), which shares a view
        $respondentTypePlural = $respondentType.'s';

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($survey->community_id);
        $this->set([
            'communityId' => $community->id,
            'surveyType' => $survey->type,
            'titleForLayout' => $community->name.': Invite Community '.ucwords($respondentTypePlural),
        ]);
        $this->set(compact(
            'allRespondents',
            'approvedRespondents',
            'communityId',
            'respondentTypePlural',
            'surveyId',
            'unaddressedUnapprovedRespondents'
        ));
        $this->render('..'.DS.'..'.DS.'Client'.DS.'Surveys'.DS.'invite');
    }

    public function remind($surveyId)
    {
        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($surveyId);
        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($survey->community_id);

        if ($this->request->is('post')) {
            $Mailer = new Mailer();
            $sender = $this->Auth->user();
            if ($Mailer->sendReminders($surveyId, $sender)) {
                $this->Flash->success('Reminder email successfully sent');
                return $this->redirect([
                    'prefix' => 'admin',
                    'controller' => 'Surveys',
                    'action' => 'view',
                    $community->id,
                    $survey->type
                ]);
            }

            $msg = 'There was an error sending reminder emails.';
            $adminEmail = Configure::read('admin_email');
            $msg .= ' Email <a href="mailto:'.$adminEmail.'">'.$adminEmail.'</a> for assistance.';
            $this->Flash->error($msg);

            // Redirect so that hitting refresh won't re-send POST request
            return $this->redirect([
                'prefix' => 'admin',
                'controller' => 'Surveys',
                'action' => 'remind',
                $survey->id
            ]);
        }

        $respondentsTable = TableRegistry::get('Respondents');
        $unresponsive = $respondentsTable->getUnresponsive($surveyId);
        $this->set([
            'community' => $community,
            'survey' => $survey,
            'titleForLayout' => 'Send Reminders to Community '.ucwords($survey->type).'s',
            'unresponsive' => $unresponsive,
            'unresponsiveCount' => count($unresponsive)
        ]);
        $this->render('..'.DS.'..'.DS.'Client'.DS.'Surveys'.DS.'remind');
    }
}
