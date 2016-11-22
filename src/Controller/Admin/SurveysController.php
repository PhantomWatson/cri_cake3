<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Mailer\Mailer;
use App\Model\Entity\Community;
use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

class SurveysController extends AppController
{

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('SurveyProcessing');
    }

    /**
     * View method
     *
     * @param int|null $communityId Community ID
     * @param int|null $surveyType Survey type
     * @return \Cake\Network\Response|null
     */
    public function view($communityId = null, $surveyType = null)
    {
        if (! in_array($surveyType, ['official', 'organization'])) {
            throw new NotFoundException("Unknown questionnaire type: $surveyType");
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

        $this->prepareAdminHeader();
        $this->set([
            'community' => $community,
            'currentlyActive' => $survey->active,
            'survey' => $survey,
            'titleForLayout' => $community->name . ': ' . ucwords($surveyType) . 's Questionnaire Overview'
        ]);
    }

    /**
     * Link method
     *
     * @param int|null $communityId Community ID
     * @param int|null $surveyType Survey type
     * @return \Cake\Network\Response|null
     */
    public function link($communityId = null, $surveyType = null)
    {
        if (! in_array($surveyType, ['official', 'organization'])) {
            throw new NotFoundException("Unknown questionnaire type: $surveyType");
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
                if ($isNew) {
                    $message = 'Questionnaire successfully linked to this community';
                } else {
                    $message = 'Questionnaire details updated';
                }
                $this->Flash->success($message);
                $this->redirect([
                    'action' => 'view',
                    $communityId,
                    $surveyType
                ]);
            } else {
                $msg = 'There was an error ';
                $msg .= $survey->isNew() ? 'linking questionnaire' : 'updating questionnaire details';
                $msg .= '. Please try again or contact an administrator for assistance.';
                $this->Flash->error($msg);
            }
        }

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($communityId);

        $this->prepareAdminHeader();
        $this->set([
            'community' => $community,
            'qnaIdFields' => $this->Surveys->getQnaIdFieldNames(),
            'survey' => $survey,
            'titleForLayout' => $community->name . ': ' . ucwords($surveyType) . 's Questionnaire Link'
        ]);
    }

    /**
     * Sets variables for the view used in survey overview page
     *
     * @param Survey $survey Survey
     * @param Community $community Community
     */
    private function prepareSurveyStatus($survey, $community)
    {
        $surveyStatus = $this->Surveys->getStatus($survey->community_id, $survey->type);

        /* Determines if this survey is currently being auto-imported
         * (because the community is in an appropriate stage of the CRI process) */
        $stageForAutoImport = $survey->type == 'official' ? 2 : 3;
        $isAutomaticallyImported = floor($community->score) == $stageForAutoImport && $survey->active;

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
        $userId = $this->Auth->user('id');

        if ($this->request->is('post')) {
            $submitMode = $this->request->data('submit_mode');
            if (stripos($submitMode, 'send') !== false) {
                $this->SurveyProcessing->sendInvitations($communityId, $respondentType, $surveyId);
                $this->SurveyProcessing->clearSavedInvitations($surveyId, $userId);
            } elseif (stripos($submitMode, 'save') !== false) {
                list($saveResult, $msg) = $this->SurveyProcessing->saveInvitations(
                    $this->request->data('invitees'),
                    $surveyId,
                    $userId
                );
                if ($saveResult) {
                    $this->Flash->success($msg);
                    return $this->redirect([
                        'prefix' => 'admin',
                        'controller' => 'Communities',
                        'action' => 'index'
                    ]);
                } else {
                    $this->Flash->error($msg);
                }
            } else {
                $msg = 'There was an error submitting your form. ';
                $msg .= 'Please try again or email cri@bsu.edu for assistance.';
                $this->Flash->error($msg);
            }
        } else {
            $this->request->data['invitees'] = $this->SurveyProcessing->getSavedInvitations($surveyId, $userId);
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
        $this->prepareAdminHeader();
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
            'titleForLayout' => $community->name . ': Remind Community '.ucwords($survey->type).'s',
            'unresponsive' => $unresponsive,
            'unresponsiveCount' => count($unresponsive)
        ]);
        $this->prepareAdminHeader();
        $this->render('..'.DS.'..'.DS.'Client'.DS.'Surveys'.DS.'remind');
    }

    public function activate($surveyId)
    {
        $communitiesTable = TableRegistry::get('Communities');
        $survey = $this->Surveys->get($surveyId);
        $currentlyActive = $survey->active;
        $community = $communitiesTable->get($survey->community_id);
        if ($this->request->is('put')) {
            $survey = $this->Surveys->patchEntity($survey, $this->request->data());
            if ($survey->errors()) {
                $this->Flash->error('There was an error updating the selected questionnaire');
            } elseif ($this->Surveys->save($survey)) {
                $currentlyActive = $this->request->data('active');
                $msg = 'Questionnaire ' . ($this->request->data('active') ? 'activated' : 'deactivated');
                $this->Flash->success($msg);
            } else {
                $this->Flash->error('There was an error updating the selected questionnaire');
            }
        }
        $this->prepareAdminHeader();
        $this->set([
            'community' => $community,
            'currentlyActive' => $currentlyActive,
            'survey' => $survey,
            'titleForLayout' => $community->name . ': ' . ucwords($survey->type) . 's Questionnaire Activation'
        ]);
    }
}
