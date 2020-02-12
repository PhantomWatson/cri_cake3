<?php
declare(strict_types=1);

namespace App\Controller\Client;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\NotFoundException;
use Cake\Mailer\MailerAwareTrait;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * Surveys Controller
 *
 * @property \App\Model\Table\SurveysTable $Surveys
 * @property \App\Controller\Component\SurveyProcessingComponent $SurveyProcessing
 */
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
     * Invite method
     *
     * @param string|null $respondentTypePlural Either 'officials' or 'organizations'
     * @return \Cake\Http\Response
     * @throws \Cake\Network\Exception\BadRequestException
     */
    public function invite($respondentTypePlural = null)
    {
        // Find and validate community
        $clientId = $this->getClientId();
        if (! $clientId) {
            return $this->chooseClientToImpersonate();
        }
        /** @var \App\Model\Table\CommunitiesTable $communitiesTable */
        $communitiesTable = TableRegistry::get('Communities');
        $communityId = $communitiesTable->getClientCommunityId($clientId);
        if (! $communityId || ! $communitiesTable->exists(['id' => $communityId])) {
            $msg = 'Sorry, we couldn\'t find the community corresponding with your account (#' . $clientId . ')';
            throw new NotFoundException($msg);
        }

        if ($respondentTypePlural !== 'officials' && $respondentTypePlural != 'organizations') {
            throw new BadRequestException('Questionnaire type not specified');
        }

        $respondentType = str_replace('s', '', $respondentTypePlural);
        $surveyId = $this->Surveys->getSurveyId($communityId, $respondentType);
        if (! $this->Surveys->isActive($surveyId)) {
            throw new ForbiddenException('New invitations cannot be sent out: Questionnaire is inactive');
        }

        $userId = $this->Auth->user('id');
        if ($this->request->is('post')) {
            $invitees = [];
            $submitMode = $this->request->getData('submit_mode');
            if (stripos($submitMode, 'send') !== false) {
                $this->SurveyProcessing->sendInvitations($communityId, $respondentType, $surveyId);
                $this->SurveyProcessing->clearSavedInvitations($surveyId, $userId);
                $invitees = $this->SurveyProcessing->pendingInvitees;
            } elseif (stripos($submitMode, 'save') !== false) {
                [$saveResult, $msg] = $this->SurveyProcessing->saveInvitations(
                    $this->request->getData('invitees'),
                    $surveyId,
                    $userId
                );
                if ($saveResult) {
                    $this->Flash->success($msg);

                    return $this->redirect([
                        'prefix' => 'clients',
                        'controller' => 'Communities',
                        'action' => 'index',
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

        /** @var \App\Model\Table\RespondentsTable $respondentsTable */
        $respondentsTable = TableRegistry::get('Respondents');
        $approvedRespondents = $respondentsTable->getApprovedList($surveyId);
        $unaddressedUnapprovedRespondents = $respondentsTable->getUnaddressedUnapprovedList($surveyId);
        $allRespondents = array_merge($approvedRespondents, $unaddressedUnapprovedRespondents);

        $survey = $this->Surveys->get($surveyId);
        $this->set([
            'surveyType' => $survey->type,
            'titleForLayout' => 'Invite Community ' . ucwords($respondentTypePlural),
        ]);
        $this->set(compact(
            'allRespondents',
            'approvedRespondents',
            'communityId',
            'invitees',
            'respondentTypePlural',
            'surveyId',
            'titleForLayout',
            'unaddressedUnapprovedRespondents'
        ));

        return null;
    }

    /**
     * Remind function
     *
     * @param string $surveyType Survey type
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\NotFoundException
     * @throws \Cake\Network\Exception\ForbiddenException
     */
    public function remind($surveyType)
    {
        $clientId = $this->getClientId();
        if (! $clientId) {
            return $this->chooseClientToImpersonate();
        }

        /** @var \App\Model\Table\CommunitiesTable $communitiesTable */
        $communitiesTable = TableRegistry::get('Communities');
        $communityId = $communitiesTable->getClientCommunityId($clientId);
        if (! $communityId) {
            throw new NotFoundException('Your account is not currently assigned to a community');
        }

        /** @var \App\Model\Table\SurveysTable $surveysTable */
        $surveysTable = TableRegistry::get('Surveys');
        $surveyId = $surveysTable->getSurveyId($communityId, $surveyType);
        $survey = $surveysTable->get($surveyId);
        if (! $survey->active) {
            throw new ForbiddenException('Reminders cannot currently be sent out: Questionnaire is inactive');
        }

        /** @var \App\Model\Table\RespondentsTable $respondentsTable */
        $respondentsTable = TableRegistry::get('Respondents');
        if ($this->request->is('post')) {
            $sender = $this->Auth->user();
            $recipients = $respondentsTable->getUnresponsive($surveyId);
            $recipients = Hash::extract($recipients, '{n}.email');
            try {
                /** @var \Queue\Model\Table\QueuedJobsTable $queuedJobs */
                $queuedJobs = TableRegistry::get('Queue.QueuedJobs');
                foreach ($recipients as $recipient) {
                    $queuedJobs->createJob(
                        'Reminder',
                        compact('surveyId', 'sender', 'recipient'),
                        ['reference' => $recipient]
                    );
                }
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
                    'prefix' => 'client',
                    'controller' => 'Surveys',
                    'action' => 'remind',
                    $survey->type,
                ]);
            }

            $this->Flash->success('Reminder email successfully sent');

            // Dispatch event
            $recipients = $respondentsTable->getUnresponsive($surveyId);
            $event = new Event('Model.Survey.afterRemindersSent', $this, ['meta' => [
                'communityId' => $survey->community_id,
                'surveyId' => $surveyId,
                'surveyType' => $survey->type,
                'remindedCount' => count($recipients),
            ]]);
            $this->getEventManager()->dispatch($event);

            return $this->redirect([
                'prefix' => 'client',
                'controller' => 'Communities',
                'action' => 'index',
            ]);
        }

        $unresponsive = $respondentsTable->getUnresponsive($surveyId);
        $this->set([
            'community' => $communitiesTable->get($communityId),
            'survey' => $survey,
            'titleForLayout' => 'Send Reminders to Community ' . ucwords($survey->type) . 's',
            'unresponsive' => $unresponsive,
            'unresponsiveCount' => count($unresponsive),
        ]);

        return null;
    }
}
