<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class SurveyProcessingComponent extends Component
{
    public $components = ['Flash', 'Auth'];

    public $approvedRespondents = [];
    public $communityId = null;
    public $errorEmails = [];
    public $invitees = [];
    public $recipients = [];
    public $redundantEmails = [];
    public $respondentType = null;
    public $successEmails = [];
    public $surveyId = null;
    public $unaddressedUnapprovedRespondents = [];
    public $uninvApprovedEmails = [];

    public function processInvitations($communityId, $respondentType, $surveyId)
    {
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

        $this->sendInvitationEmails();
        $this->setInvitationFlashMessages();
        $this->request->data = [];
    }

    private function setInvitees()
    {
        $invitees = $this->request->data('invitees');
        $invitees = is_array($invitees) ? $invitees : [];
        $this->invitees = $invitees;
    }

    /**
     * Clean name, email, and title and remove any invitees with no email address
     */
    private function cleanInvitees()
    {
        foreach ($this->invitees as $i => &$invitee) {
            foreach (['name', 'email', 'title'] as $field) {
                $invitee[$field] = trim($invitee[$field]);
            }

            $invitee['email'] = strtolower($invitee['email']);

            if (empty($invitee['email'])) {
                unset($this->invitees[$i]);
            }
        }
    }

    /**
     * Removes invitees if they've already been invited / approved
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

    private function approveInvitee($invitee)
    {
        $this->uninvApprovedEmails[] = $invitee['email'];
        $respondentsTable = TableRegistry::get('Respondents');
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
     * @param string $email
     * @return boolean
     */
    private function isUnapproved($email)
    {
        return in_array($email, $this->unaddressedUnapprovedRespondents);
    }

    /**
     * Adds a new respondent and adds them to the invitation email queue
     *
     * @param $invitee array
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
        $errors = $respondent->errors();
        if (empty($errors) && $respondentsTable->save($respondent)) {
            $this->recipients[] = $respondent->email;
            $this->approvedRespondents[] = $respondent->email;
        } else {
            $this->errorEmails[] = $invitee['email'];
            if (Configure::read('debug')) {
                $this->Flash->dump($respondent->errors());
            }
        }
    }

    private function sendInvitationEmails()
    {
        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($this->surveyId);

        $communitiesTable = TableRegistry::get('Communities');
        $clients = $communitiesTable->getClients($this->communityId);

        $senderEmail = $this->Auth->user('email');
        $senderName = $this->Auth->user('name');
        $result = $surveysTable->sendInvitationEmails($this->recipients, $clients, $survey, $senderEmail, $senderName);

        if ($result) {
            $this->successEmails = $this->recipients;
        } else {
            $this->errorEmails = $this->recipients;
        }
    }

    public function setInvitationFlashMessages()
    {
        $seCount = count($this->successEmails);
        if ($seCount) {
            $list = $this->arrayToList($this->successEmails);
            $msg = 'Survey '.__n('invitation', 'invitations', $seCount).' sent to '.$list;
            $this->Flash->success($msg);
        }

        $reCount = count($this->redundantEmails);
        if ($reCount) {
            $list = $this->arrayToList($this->redundantEmails);
            $msg = $list.__n(' has', ' have', $reCount).' already received a survey invitation';
            $this->Flash->set($msg);
        }

        $eeCount = count($this->errorEmails);
        if ($eeCount) {
            $list = $this->arrayToList($this->errorEmails);
            $msg = 'There was an error inviting '.$list.'. Please try again or contact an administrator if you need assistance.';
            $this->Flash->error($msg);
        }

        $rieCount = count($this->uninvApprovedEmails);
        if ($rieCount) {
            $list = $this->arrayToList($this->uninvApprovedEmails);
            $msg = 'The uninvited '.__n('response', 'responses', $rieCount).' received from '.$list.__n(' has', ' have', $rieCount).' been approved';
            $this->Flash->success($msg);
        }
    }

    /**
     * Accepts an array of stringy variables and returns a comma-delimited list with an optional conjunction before the last element
     * @param array $array
     * @param string $conjunction
     * @return string
     */
    public function arrayToList($array, $conjunction = 'and')
    {
        $count = count($array);
        if (! $count) {
            return '';
        } elseif ($count == 1) {
            return $array[0];
        } elseif ($count > 1) {
            if ($conjunction) {
                $last_element = array_pop($array);
                array_push($array, $conjunction.' '.$last_element);
            }
            if ($count == 2) {
                return implode(' ', $array);
            } else {
                return implode(', ', $array);
            }
        }
    }
}
