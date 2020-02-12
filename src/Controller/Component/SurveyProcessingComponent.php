<?php
namespace App\Controller\Component;

use App\Model\Entity\InvitationFormData;
use App\Model\Entity\Respondent;
use App\Model\Table\RespondentsTable;
use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Queue\Model\Table\QueuedJobsTable;

/**
 * @property \Cake\Controller\Component\FlashComponent $Flash
 * @property \Cake\Controller\Component\AuthComponent $Auth
 */
class SurveyProcessingComponent extends Component
{
    use MailerAwareTrait;

    public $components = ['Flash', 'Auth'];

    public $approvedRespondents = [];
    public $communityId = null;
    public $errorEmails = [];
    public $invitees = [];
    public $pendingInvitees = [];
    public $recipients = [];
    public $redundantEmails = [];
    public $respondentType = null;
    public $successEmails = [];
    public $surveyId = null;
    public $unaddressedUnapprovedRespondents = [];
    public $uninvApprovedEmails = [];

    /**
     * Saves invitation form data for editing or sending later
     *
     * @param array $formData Form data
     * @param int $surveyId Survey ID
     * @param int $userId User ID
     * @return array [TRUE or FALSE for success, success/error message]
     */
    public function saveInvitations($formData, $surveyId, $userId)
    {
        $formDataTable = TableRegistry::get('InvitationFormData');
        $existingRecord = $formDataTable->find('all')
            ->where([
                'survey_id' => $surveyId,
                'user_id' => $userId
            ])
            ->first();
        if ($existingRecord) {
            $existingRecord = $formDataTable->patchEntity($existingRecord, [
                'data' => serialize($formData)
            ]);
            $errors = $existingRecord->getErrors();
            $saveResult = $formDataTable->save($existingRecord);
        } else {
            $savedData = $formDataTable->newEntity([
                'survey_id' => $surveyId,
                'user_id' => $userId,
                'data' => serialize($formData)
            ]);
            $errors = $savedData->getErrors();
            $saveResult = $formDataTable->save($savedData);
        }
        if ($errors || ! $saveResult) {
            $msg = 'There was an error saving your form data. ';
            $msg .= 'Please try again or email cri@bsu.edu for assistance.';

            return [false, $msg];
        } else {
            $msg = 'Invitation data saved. ';
            $msg .= 'You can return to the questionnaire invitation page later to send the saved invitations.';

            return [true, $msg];
        }
    }

    /**
     * Removes the specified InvitationFormData record
     *
     * @param int $surveyId Survey ID
     * @param int $userId User ID
     * @return bool
     */
    public function clearSavedInvitations($surveyId, $userId)
    {
        $formDataTable = TableRegistry::get('InvitationFormData');
        $result = $formDataTable->find('all')
            ->select(['id'])
            ->where([
                'survey_id' => $surveyId,
                'user_id' => $userId
            ])
            ->first();
        if ($result) {
            $savedData = $formDataTable->get($result->id);

            return (bool)$formDataTable->delete($savedData);
        }

        return true;
    }

    /**
     * Returns an array representation of the specified saved invitation form data
     *
     * @param int $surveyId Survey ID
     * @param int $userId User ID
     * @return array
     */
    public function getSavedInvitations($surveyId, $userId)
    {
        $formDataTable = TableRegistry::get('InvitationFormData');

        /** @var InvitationFormData $savedData */
        $savedData = $formDataTable->find('all')
            ->select(['data'])
            ->where([
                'survey_id' => $surveyId,
                'user_id' => $userId
            ])
            ->first();

        return $savedData ? unserialize($savedData->data) : [];
    }

    /**
     * Creates respondent records and sends invitation emails
     *
     * @param int $communityId Community ID
     * @param string $respondentType Respondent / survey type
     * @param int $surveyId Survey ID
     * @return void
     */
    public function sendInvitations($communityId, $respondentType, $surveyId)
    {
        /** @var RespondentsTable $respondentsTable */
        $respondentsTable = TableRegistry::get('Respondents');
        $this->approvedRespondents = $respondentsTable->getApprovedList($surveyId);
        $this->unaddressedUnapprovedRespondents = $respondentsTable->getUnaddressedUnapprovedList($surveyId);
        $this->communityId = $communityId;
        $this->respondentType = $respondentType;
        $this->surveyId = $surveyId;

        $this->setInvitees();
        $this->cleanInvitees();
        $this->removeApproved();

        foreach ($this->invitees as $i => $invitee) {
            if ($this->isUnapproved($invitee['email'])) {
                $this->approveInvitee($invitee);
                continue;
            }

            $this->createRespondent($invitee);
        }

        try {
            /** @var QueuedJobsTable $queuedJobs */
            $queuedJobs = TableRegistry::get('Queue.QueuedJobs');
            foreach ($this->recipients as $recipient) {
                $queuedJobs->createJob(
                    'Invitation',
                    [
                        'surveyId' => $this->surveyId,
                        'communityId' => $this->communityId,
                        'senderEmail' => $this->Auth->user('email'),
                        'senderName' => $this->Auth->user('name'),
                        'recipient' => $recipient
                    ],
                    ['reference' => $recipient]
                );
            }

            $this->successEmails = array_merge($this->successEmails, $this->recipients);
            $this->removeFromPending($this->recipients);

            // Dispatch event
            $surveysTable = TableRegistry::get('Surveys');
            $survey = $surveysTable->get($this->surveyId);
            $event = new Event('Model.Survey.afterInvitationsSent', $this, ['meta' => [
                'communityId' => $this->communityId,
                'surveyId' => $this->surveyId,
                'surveyType' => $survey->type,
                'invitedCount' => count($this->successEmails)
            ]]);
            $this->_registry->getController()->getEventManager()->dispatch($event);
        } catch (\Exception $e) {
            $this->errorEmails = array_merge($this->errorEmails, $this->recipients);
            $class = get_class($e);
            $exceptionMsg = $e->getMessage();
            $this->Flash->error("$class: $exceptionMsg");
        }
        $this->setInvitationFlashMessages();
    }

    /**
     * Sets $this->invitees based on request data
     *
     * @return void
     */
    private function setInvitees()
    {
        $invitees = (array)$this->getController()->request->getData('invitees');
        $this->invitees = $invitees;
    }

    /**
     * Clean name, email, and title and remove any invitees with no email address
     *
     * @return void
     */
    private function cleanInvitees()
    {
        foreach ($this->invitees as $i => &$invitee) {
            foreach (['name', 'email', 'title'] as $field) {
                $invitee[$field] = trim($invitee[$field]);
            }

            $invitee['email'] = strtolower($invitee['email']);

            // Ignore blank rows
            if ($invitee['name'] . $invitee['email'] . $invitee['title'] == '') {
                unset($this->invitees[$i]);
            }
        }
    }

    /**
     * Removes invitees if they've already been invited / approved
     *
     * @return void
     */
    private function removeApproved()
    {
        foreach ($this->invitees as $i => $invitee) {
            if (in_array($invitee['email'], $this->approvedRespondents)) {
                $this->redundantEmails[] = $invitee['email'];
                unset($this->invitees[$i]);
            }
        }
    }

    /**
     * Approves an invitee and updates their name and title if provided
     *
     * @param array $invitee Invitee array
     * @return void
     */
    private function approveInvitee($invitee)
    {
        $this->uninvApprovedEmails[] = $invitee['email'];

        /** @var RespondentsTable $respondentsTable */
        $respondentsTable = TableRegistry::get('Respondents');

        /** @var Respondent $respondent */
        $respondent = $respondentsTable->findBySurveyIdAndEmail($this->surveyId, $invitee['email'])->first();

        // Approve
        $respondent->approved = 1;

        // Update details
        foreach (['name', 'title'] as $field) {
            if ($invitee[$field]) {
                $respondent->$field = $invitee[$field];
            }
        }

        // Save
        if (! $respondentsTable->save($respondent)) {
            $this->errorEmails[] = $invitee['email'];
        }

        // Add to approved list
        $this->approvedRespondents[] = $invitee['email'];

        // Remove from unapproved list
        $k = array_search($invitee['email'], $this->unaddressedUnapprovedRespondents);
        unset($this->unaddressedUnapprovedRespondents[$k]);
    }

    /**
     * Returns true if email corresponds to an uninvited respondent pending approval / dismissal
     *
     * @param string $email Email address
     * @return bool
     */
    private function isUnapproved($email)
    {
        return in_array($email, $this->unaddressedUnapprovedRespondents);
    }

    /**
     * Adds a new respondent and adds them to the invitation email queue
     *
     * @param array $invitee Invitee array
     * @return void
     */
    private function createRespondent($invitee)
    {
        $respondentsTable = TableRegistry::get('Respondents');
        $respondent = $respondentsTable->newEntity([
            'approved' => 1,
            'community_id' => $this->communityId,
            'email' => $invitee['email'],
            'invited' => true,
            'name' => $invitee['name'],
            'survey_id' => $this->surveyId,
            'title' => $invitee['title'],
            'type' => $this->respondentType
        ]);
        $errors = $respondent->getErrors();
        if (empty($errors) && $respondentsTable->save($respondent)) {
            $this->recipients[] = $respondent->email;
            $this->approvedRespondents[] = $respondent->email;
        } else {
            $this->errorEmails[] = $invitee['email'];

            if (isset($errors['email']['validFormat'])) {
                $this->Flash->error('The email address "' . $invitee['email'] . '" is not formatted correctly.');
            } elseif ($invitee['email'] == '') {
                $this->Flash->error('The email address for ' . $invitee['name'] . ' was left blank.');
            } else {
                $this->Flash->error('There was an error sending an invitation to ' . $invitee['email'] . '. ' .
                    'Make sure that all information is included and formatted correctly.');
            }

            // Manually set this field so that its value will appear in the form despite being invalid
            $respondent->email = $invitee['email'];

            $this->pendingInvitees[] = $respondent;
        }
    }

    /**
     * Removes the matching invitee from $this->pendingInvitees
     *
     * @param array|string $emails Array of emails addresses or a single email address
     * @return void
     */
    private function removeFromPending($emails)
    {
        foreach ((array)$emails as $email) {
            foreach ($this->pendingInvitees as $k => $invitee) {
                if ($invitee->email == $email) {
                    unset($this->pendingInvitees[$k]);
                    break;
                }
            }
        }
    }

    /**
     * Sets flash messages based on component properties successEmails, redundantEmails, and errorEmails
     *
     * @return void
     */
    public function setInvitationFlashMessages()
    {
        $seCount = count($this->successEmails);
        if ($seCount) {
            $list = $this->arrayToList($this->successEmails);
            $msg = 'Questionnaire ' .
                __n('invitation', 'invitations', $seCount) .
                " sent to $list";
            $this->Flash->success($msg);
        }

        $reCount = count($this->redundantEmails);
        if ($reCount) {
            $list = $this->arrayToList($this->redundantEmails);
            $msg = $list .
                __n(' has', ' have', $reCount) .
                ' already received a questionnaire invitation';
            $this->Flash->set($msg);
        }

        $eeCount = count($this->errorEmails);
        if ($eeCount) {
            $list = $this->arrayToList($this->errorEmails, 'or');
            $msg = __n('An invitation', 'Invitations', $eeCount) .
                " could not be sent to $list." .
                ' Please correct any indicated errors and try again,' .
                ' or contact an administrator if you need assistance.';
            $this->Flash->error($msg);
        }

        $rieCount = count($this->uninvApprovedEmails);
        if ($rieCount) {
            $list = $this->arrayToList($this->uninvApprovedEmails);
            $msg = 'The uninvited ' .
                __n('response', 'responses', $rieCount) .
                " received from $list " .
                __n('has', 'have', $rieCount) .
                ' been approved';
            $this->Flash->success($msg);
        }
    }

    /**
     * Accepts an array of stringy variables and returns a comma-delimited list with an optional conjunction
     * before the last element
     *
     * @param array $array Arbitrary array
     * @param string $conjunction Such as 'and' (optional)
     * @return string
     */
    public function arrayToList($array, $conjunction = 'and')
    {
        $count = count($array);

        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return $array[0];
        }

        if ($conjunction) {
            $lastElement = array_pop($array);
            array_push($array, $conjunction . ' ' . $lastElement);
        }

        if ($count === 2) {
            return implode(' ', $array);
        }

        return implode(', ', $array);
    }

    /**
     * Returns an array of the most recent responses for each of this survey's respondents
     *
     * @param int $surveyId Survey ID
     * @return array
     */
    public function getCurrentResponses($surveyId)
    {
        $responsesTable = TableRegistry::get('Responses');
        $responses = $responsesTable->find('all')
            ->where(['Responses.survey_id' => $surveyId])
            ->contain([
                'Respondents' => function ($q) {
                    /** @var Query $q */

                    return $q->select(['id', 'email', 'name', 'title', 'approved']);
                }
            ])
            ->order(['Responses.response_date' => 'DESC'])
            ->all();

        // Only return the most recent response for each respondent
        $retval = [];
        foreach ($responses as $i => $response) {
            $respondentId = $response['respondent']['id'];

            if (isset($retval[$respondentId]['revision_count'])) {
                $retval[$respondentId]['revision_count']++;
                continue;
            }

            $retval[$respondentId] = $response;
            $retval[$respondentId]['revision_count'] = 0;
        }

        return $retval;
    }

    /**
     * Returns the sum of alignments between respondent PWRRR ranks and either local-area or parent-area actual ranks
     *
     * @param array $responses Responses array
     * @param string $alignmentField Alignment field name (alignment_vs_local or alignment_vs_parent)
     * @return int
     */
    public static function getAlignmentSum($responses, $alignmentField)
    {
        $alignmentSum = 0;
        foreach ($responses as $i => $response) {
            if ($response['respondent']['approved'] == 1) {
                $alignmentSum += $response->$alignmentField;
            }
        }

        return $alignmentSum;
    }

    /**
     * Returns the count of all approved respondents
     *
     * @param array $responses Responses array
     * @return int
     */
    public static function getApprovedCount($responses)
    {
        $approvedCount = 0;
        foreach ($responses as $i => $response) {
            if ($response['respondent']['approved'] == 1) {
                $approvedCount++;
            }
        }

        return $approvedCount;
    }
}
