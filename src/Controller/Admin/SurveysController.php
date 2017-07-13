<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\Community;
use App\SurveyMonkey\SurveyMonkey;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Mailer\MailerAwareTrait;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Hash;

class SurveysController extends AppController
{
    use MailerAwareTrait;

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('SurveyProcessing');
        $this->loadComponent('RequestHandler');
    }

    /**
     * View method
     *
     * @param string|null $communitySlug Community slug
     * @param int|null $surveyType Survey type
     * @return \Cake\Http\Response|null
     * @throws NotFoundException
     */
    public function view($communitySlug = null, $surveyType = null)
    {
        if (! in_array($surveyType, ['official', 'organization'])) {
            throw new NotFoundException("Unknown questionnaire type: $surveyType");
        }

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->find('slugged', ['slug' => $communitySlug])->first();
        if (! $community) {
            throw new NotFoundException("Community not found");
        }

        $surveyId = $this->Surveys->getSurveyId($community->id, $surveyType);

        if ($surveyId) {
            $survey = $this->Surveys->get($surveyId);
        } else {
            return $this->redirect([
                'action' => 'link',
                $communitySlug,
                $surveyType
            ]);
        }

        if ($survey->get('id')) {
            $this->prepareSurveyStatus($survey, $community);
        }

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
     * @param string|null $communitySlug Community slug
     * @param int|null $surveyType Survey type
     * @return void
     * @throws NotFoundException
     */
    public function link($communitySlug = null, $surveyType = null)
    {
        if (! in_array($surveyType, ['official', 'organization'])) {
            throw new NotFoundException("Unknown questionnaire type: $surveyType");
        }

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->find('slugged', ['slug' => $communitySlug])->first();
        if (! $community) {
            throw new NotFoundException("Community not found");
        }

        $surveyId = $this->Surveys->getSurveyId($community->id, $surveyType);

        if ($surveyId) {
            $survey = $this->Surveys->get($surveyId);
        } else {
            $survey = $this->Surveys->newEntity();
            $survey->community_id = $community->id;
            $survey->type = $surveyType;
        }

        if ($this->request->is(['post', 'put'])) {
            $survey = $this->Surveys->patchEntity($survey, $this->request->getData());
            $errors = $survey->getErrors();
            $isNew = $survey->isNew();
            if (empty($errors) && $this->Surveys->save($survey)) {
                // Flash message
                if ($isNew) {
                    $message = 'Questionnaire successfully linked to this community';
                } else {
                    $message = 'Questionnaire details updated';
                }
                $this->Flash->success($message);

                // Events
                $eventName = $isNew ? 'Model.Survey.afterLinked' : 'Model.Survey.afterLinkUpdated';
                $event = new Event($eventName, $this, ['meta' => [
                    'communityId' => $community->id,
                    'surveyId' => $survey->id,
                    'surveyType' => $surveyType
                ]]);
                $this->eventManager()->dispatch($event);
                if ($isNew && $survey->active) {
                    $event = new Event('Model.Survey.afterActivate', $this, ['meta' => [
                        'communityId' => $community->id,
                        'surveyId' => $survey->id,
                        'surveyType' => $survey->type
                    ]]);
                    $this->eventManager()->dispatch($event);
                }

                $this->redirect([
                    'action' => 'view',
                    $communitySlug,
                    $surveyType
                ]);
            } else {
                $msg = 'There was an error ';
                $msg .= $survey->isNew() ? 'linking questionnaire' : 'updating questionnaire details';
                $msg .= '. Please try again or contact an administrator for assistance.';
                $errorMessages = Hash::extract($errors, '{s}.{s}.{*}');
                $msg .= '<br />Details: ' . implode('; ', $errorMessages);
                $this->Flash->error($msg);
            }
        }

        // Display warning about activating an org survey before deactivating an officials survey
        $warning = null;
        if ($surveyType == 'organization') {
            $officialsSurvey = $this->Surveys->find('all')
                ->select(['id', 'active'])
                ->where([
                    'community_id' => $community->id,
                    'type' => 'official'
                ])
                ->first();
            if ($officialsSurvey && $officialsSurvey->active) {
                $url = Router::url([
                    'prefix' => 'admin',
                    'controller' => 'Surveys',
                    'action' => 'activate',
                    $officialsSurvey->id
                ]);
                $warning =
                    'This community\'s officials questionnaire is still active. ' .
                    'It is recommended that <a href="' . $url . '">that questionnaire be deactivated</a> ' .
                    'before this one is activated.';
            }
        }
        $this->set([
            'community' => $community,
            'qnaIdFields' => $this->Surveys->getQnaIdFieldNames(),
            'survey' => $survey,
            'titleForLayout' => $community->name . ': ' . ucwords($surveyType) . 's Questionnaire Link',
            'warning' => $warning
        ]);
    }

    /**
     * Sets variables for the view used in survey overview page
     *
     * @param Survey $survey Survey
     * @param Community $community Community
     * @return void
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
            'isActive' => $this->Surveys->isActive($survey->id),
            'percentInvitedResponded' => $surveyStatus['percent_invited_responded'],
            'responsesChecked' => $surveyStatus['responses_checked'],
            'stageForAutoImport' => $stageForAutoImport,
            'uninvitedRespondentCount' => $surveyStatus['uninvited_respondent_count']
        ]);
    }

    /**
     * Method for /admin/surveys/invite
     *
     * @param int|null $surveyId Survey ID
     * @return \Cake\Http\Response|null
     */
    public function invite($surveyId = null)
    {
        $survey = $this->Surveys->get($surveyId);
        if (! $survey->active) {
            $msg = 'Invitations cannot currently be sent out for that questionnaire because it is inactive.';
            $this->Flash->error($msg);

            return $this->redirect($this->request->referer());
        }

        $communityId = $survey->community_id;
        $respondentType = $survey->type;
        $userId = $this->Auth->user('id');

        if ($this->request->is('post')) {
            $invitees = [];
            $submitMode = $this->request->getData('submit_mode');
            if (stripos($submitMode, 'send') !== false) {
                $this->SurveyProcessing->sendInvitations($communityId, $respondentType, $surveyId);
                $this->SurveyProcessing->clearSavedInvitations($surveyId, $userId);
                $invitees = $this->SurveyProcessing->pendingInvitees;
            } elseif (stripos($submitMode, 'save') !== false) {
                list($saveResult, $msg) = $this->SurveyProcessing->saveInvitations(
                    $this->request->getData('invitees'),
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
            $invitees = $this->SurveyProcessing->getSavedInvitations($surveyId, $userId);
        }

        $respondentsTable = TableRegistry::get('Respondents');
        $approvedRespondents = $respondentsTable->getApprovedList($surveyId);
        $unaddressedUnapprovedRespondents = $respondentsTable->getUnaddressedUnapprovedList($surveyId);
        $allRespondents = array_merge($approvedRespondents, $unaddressedUnapprovedRespondents);

        // Looks dumb, but this is because it's the parameter for client_invite(), which shares a view
        $respondentTypePlural = $respondentType . 's';

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($survey->community_id);
        $this->set([
            'community' => $community,
            'communityId' => $community->id,
            'surveyType' => $survey->type,
            'titleForLayout' => $community->name . ': Invite Community ' . ucwords($respondentTypePlural),
        ]);
        $this->set(compact(
            'allRespondents',
            'approvedRespondents',
            'communityId',
            'invitees',
            'respondentTypePlural',
            'surveyId',
            'unaddressedUnapprovedRespondents'
        ));
    }

    /**
     * Method for /admin/surveys/remind
     *
     * @param int $surveyId Survey ID
     * @return \Cake\Http\Response|null
     */
    public function remind($surveyId)
    {
        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($surveyId);
        if (! $survey->active) {
            $msg = 'Reminders cannot currently be sent out for that questionnaire because it is inactive';
            $this->Flash->error($msg);

            return $this->redirect($this->request->referer());
        }

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($survey->community_id);

        if ($this->request->is('post')) {
            $sender = $this->Auth->user();
            try {
                $this->getMailer('Survey')->send('reminders', [$surveyId, $sender]);
            } catch (\Exception $e) {
                $adminEmail = Configure::read('admin_email');
                $class = get_class($e);
                $exceptionMsg = $e->getMessage();
                $emailLink = '<a href="mailto:' . $adminEmail . '">' . $adminEmail . '</a>';
                $msg =
                    "There was an error sending reminder emails ($class: $exceptionMsg). " .
                    "Email $emailLink for assistance.";
                $this->Flash->error($msg);

                // Redirect so that hitting refresh won't re-send POST request
                return $this->redirect([
                    'prefix' => 'admin',
                    'controller' => 'Surveys',
                    'action' => 'remind',
                    $survey->id
                ]);
            }

            $this->Flash->success('Reminder email successfully sent');

            // Dispatch event
            $respondentsTable = TableRegistry::get('Respondents');
            $recipients = $respondentsTable->getUnresponsive($surveyId);
            $event = new Event('Model.Survey.afterRemindersSent', $this, ['meta' => [
                'communityId' => $survey->community_id,
                'surveyId' => $surveyId,
                'surveyType' => $survey->type,
                'remindedCount' => count($recipients)
            ]]);
            $this->eventManager()->dispatch($event);

            return $this->redirect([
                'prefix' => 'admin',
                'controller' => 'Surveys',
                'action' => 'view',
                $community->slug,
                $survey->type
            ]);
        }

        $respondentsTable = TableRegistry::get('Respondents');
        $unresponsive = $respondentsTable->getUnresponsive($surveyId);
        $this->set([
            'community' => $community,
            'survey' => $survey,
            'titleForLayout' => $community->name . ': Remind Community ' . ucwords($survey->type) . 's',
            'unresponsive' => $unresponsive,
            'unresponsiveCount' => count($unresponsive)
        ]);
        $this->render('..' . DS . '..' . DS . 'Client' . DS . 'Surveys' . DS . 'remind');
    }

    /**
     * Method for /admin/surveys/activate
     *
     * @param int $surveyId Survey ID
     * @return void
     */
    public function activate($surveyId)
    {
        $communitiesTable = TableRegistry::get('Communities');
        $survey = $this->Surveys->get($surveyId);
        $currentlyActive = $survey->active;
        $community = $communitiesTable->get($survey->community_id);
        $warning = null;
        if ($this->request->is('put')) {
            $survey = $this->Surveys->patchEntity($survey, $this->request->getData());
            if ($survey->getErrors()) {
                $this->Flash->error('There was an error updating the selected questionnaire');
            } elseif ($this->Surveys->save($survey)) {
                $currentlyActive = $this->request->getData('active');
                $msg = 'Questionnaire ' . ($currentlyActive ? 'activated' : 'deactivated');
                $this->Flash->success($msg);

                // Event
                $eventName = $currentlyActive ? 'Model.Survey.afterActivate' : 'Model.Survey.afterDeactivate';
                $event = new Event($eventName, $this, ['meta' => [
                    'communityId' => $survey->community_id,
                    'surveyId' => $survey->id,
                    'surveyType' => $survey->type
                ]]);
                $this->eventManager()->dispatch($event);
            } else {
                $this->Flash->error('There was an error updating the selected questionnaire');
            }

        // Display warning about activating an org survey before deactivating an officials survey
        } elseif (! $currentlyActive && $survey->type == 'organization') {
            $officialsSurvey = $this->Surveys->find('all')
                ->select(['id', 'active'])
                ->where([
                    'community_id' => $community->id,
                    'type' => 'official'
                ])
                ->first();
            if ($officialsSurvey && $officialsSurvey->active) {
                $url = Router::url([
                    'prefix' => 'admin',
                    'controller' => 'Surveys',
                    'action' => 'activate',
                    $officialsSurvey->id
                ]);
                $warning =
                    'This community\'s officials questionnaire is still active. ' .
                    'It is recommended that <a href="' . $url . '">that questionnaire be deactivated</a> ' .
                    'before this one is activated.';
            }
        }

        $this->set([
            'community' => $community,
            'currentlyActive' => $currentlyActive,
            'survey' => $survey,
            'titleForLayout' => $community->name . ': ' . ucwords($survey->type) . 's Questionnaire Activation',
            'warning' => $warning
        ]);
    }

    /**
     * Resends invitations that have already been sent (or unsuccessfully sent)
     *
     * @return void
     */
    public function resendInvitations()
    {
        if ($this->request->is('post')) {
            $surveyId = $this->request->getData('surveyId');
            $survey = $this->Surveys->get($surveyId);
            $communitiesTable = TableRegistry::get('Communities');
            $community = $communitiesTable->get($survey->community_id);
            $respondentsTable = TableRegistry::get('Respondents');
            $recipients = $respondentsTable->find('list', ['valueField' => 'email'])
                ->where(['survey_id' => $surveyId])
                ->toArray();
            $usersTable = TableRegistry::get('Users');
            $user = $usersTable->get($this->request->getData('userId'));

            if ($this->request->getData('confirmed')) {
                $step = 'results';
                $this->getMailer('Survey')->send('invitations', [[
                    'surveyId' => $surveyId,
                    'communityId' => $survey->community_id,
                    'senderEmail' => $user->email,
                    'senderName' => $user->name,
                    'recipients' => $recipients
                ]]);

                // Dispatch event
                $surveysTable = TableRegistry::get('Surveys');
                $survey = $surveysTable->get($surveyId);
                $event = new Event('Model.Survey.afterInvitationsSent', $this, ['meta' => [
                    'communityId' => $survey->community_id,
                    'surveyId' => $surveyId,
                    'surveyType' => $survey->type,
                    'invitedCount' => count($recipients)
                ]]);
                $this->eventManager()->dispatch($event);

                $this->set('result', true);
            } else {
                $step = 'confirm';
                $this->set([
                    'survey' => $survey,
                    'community' => $community,
                    'recipients' => $recipients,
                    'sender' => $user
                ]);
            }
        } else {
            $step = 'input';
        }
        $this->set([
            'step' => $step,
            'titleForLayout' => 'Resend All Invitations for Survey' . (isset($surveyId) ? " #$surveyId" : null)
        ]);
    }

    /**
     * Calculates and updates survey alignment fields
     * or throws exceptions if errors encountered
     *
     * @param int $surveyId Survey ID
     * @return void
     */
    public function updateAlignment($surveyId)
    {
        $this->Surveys->updateAlignment($surveyId);
        $this->viewBuilder()->setLayout('blank');
    }

    /**
     * Populates all "aware of plan" Q&A ID fields for surveys
     *
     * @return void
     */
    public function populateAwareFields()
    {
        $surveys = $this->Surveys->find('all')
            ->select(['id', 'sm_id'])
            ->where([
                'type' => 'official',
                function ($exp, $q) {
                    return $exp->isNotNull('sm_id');
                },
                function ($exp, $q) {
                    return $exp->isNull('aware_of_plan_qid');
                }
            ])
            ->all();

        if (empty($surveys)) {
            $this->Flash->success('All surveys have aware_of_plan_qid field set.');

            return;
        }

        $this->Flash->notification(count($surveys) . " surveys to process");

        $SurveyMonkey = new SurveyMonkey();
        foreach ($surveys as $survey) {
            $msg = ["Processing survey #" . $survey->id];
            $results = $SurveyMonkey->getQuestionAndAnswerIds($survey->sm_id);
            $msg[] = "getQuestionAndAnswerIds() results:";
            $msg[] = '<pre>' . print_r($results, true) . '</pre>';
            $success = $results[0];
            if ($success && isset($results[2])) {
                $data = $results[2];
                $this->Surveys->patchEntity($survey, $data);
                if (! $this->Surveys->save($survey)) {
                    $msg[] = "Error saving";
                    $success = false;
                }
            }
            $this->Surveys->setQuestionAndAnswerIds($survey->sm_id);
            $msg[] = "Survey #" . $survey->id . " processed";
            if ($success) {
                $this->Flash->success(implode('<br />', $msg));
            } else {
                $this->Flash->error(implode('<br />', $msg));
            }
        }
        $this->Flash->notification('All surveys processed');
        $this->set('titleForLayout', 'Populate "aware of plan" fields');
    }

    /**
     * Displays a page with buttons for clearing and importing responses for each survey
     *
     * @return void
     */
    public function importAll()
    {
        $communitiesTable = TableRegistry::get('Communities');
        $communities = $communitiesTable->find('all')
            ->select(['id', 'name'])
            ->contain([
                'OfficialSurvey' => function ($q) {
                    return $q->select(['id', 'aware_of_plan_qid', 'pwrrr_qid', 'active']);
                },
                'OrganizationSurvey' => function ($q) {
                    return $q->select(['id', 'pwrrr_qid', 'active']);
                }
            ])
            ->order(['name' => 'ASC'])
            ->all();
        $this->set([
            'communities' => $communities,
            'titleForLayout' => 'Clear and Import Responses'
        ]);
    }

    /**
     * Removes all responses to the specified survey and resets that survey's fields to the state they were
     * at before any responses were imported
     *
     * @param int $surveyId Survey ID
     * @return void
     */
    public function clearResponses($surveyId)
    {
        $this->set('_serialize', ['success', 'message']);

        $survey = $this->Surveys->get($surveyId);
        $data = [
            'respondents_last_modified_date' => null,
            'responses_checked' => null,
            'alignment_vs_local' => null,
            'alignment_vs_parent' => null,
            'internal_alignment' => null,
            'alignment_calculated_date' => null,
            'import_errors' => null
        ];
        $this->Surveys->patchEntity($survey, $data);
        $result = $this->Surveys->save($survey);
        if (! $result) {
            $this->set([
                'success' => false,
                'message' => 'Survey errors: ' . print_r($result->getErrors(), true)
            ]);

            return;
        }

        $responsesTable = TableRegistry::get('Responses');
        $count = $responsesTable->find('all')
            ->where(['survey_id' => $surveyId])
            ->count();
        if ($count === 0) {
            $this->set('success', true);

            return;
        }

        $result = $responsesTable->deleteAll(['survey_id' => $surveyId]);
        $this->set('success', (bool)$result);
        if (! $result) {
            $this->set('message', 'Response delete errors: ' . print_r($result, true));
        }
    }
}
