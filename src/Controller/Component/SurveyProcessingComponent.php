<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class SurveyProcessingComponent extends Component
{
    public $components = ['Flash'];

    public function processInvitations($params)
    {
        extract($params);

        $respondentsTable = TableRegistry::get('Respondents');

        $successEmails = [];
        $redundantEmails = [];
        $uninvApprovedEmails = [];
        $errorEmails = [];
        $recipients = [];

        // Ensure $invitees is an array
        $invitees = $this->request->data('invitees');
        $invitees = empty($invitees) ? [] : $invitees;

        foreach ($invitees as $i => $invitee) {
            foreach (['name', 'email', 'title'] as $field) {
                $invitee[$field] = trim($invitee[$field]);
            }

            if (empty($invitee['email'])) {
                continue;
            }

            // Ignore if already approved
            if (in_array($invitee['email'], $approvedRespondents)) {
                $redundantEmails[] = $invitee['email'];
                continue;
            }

            // Approve an unapproved respondent
            if (in_array($invitee['email'], $unaddressedUnapprovedRespondents)) {
                $uninvApprovedEmails[] = $invitee['email'];
                $respondent = $respondentsTable->findBySurveyIdAndEmail($surveyId, $invitee['email'])->first();

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
                    $errorEmails[] = $invitee['email'];
                }

                // Add to approved list
                $approvedRespondents[] = $invitee['email'];

                // Remove from unapproved list
                $k = array_search($invitee['email'], $unaddressedUnapprovedRespondents);
                unset($unaddressedUnapprovedRespondents[$k]);

                continue;
            }

            // Create a new respondent
            $respondent = $respondentsTable->newEntity([
                'email' => $invitee['email'],
                'name' => $invitee['name'],
                'title' => $invitee['title'],
                'survey_id' => $surveyId,
                'community_id' => $communityId,
                'type' => $respondentType,
                'invited' => true,
                'approved' => 1
            ]);
            $errors = $respondent->errors();
            if (empty($errors) && $respondentsTable->save($respondent)) {
                $recipients[] = $respondent->email;
                $approvedRespondents[] = $respondent->email;
            } else {
                $errorEmails[] = $invitee['email'];
                if (Configure::read('debug')) {
                    $this->Flash->dump($respondent->errors());
                }
            }
        }

        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($surveyId);
        $communitiesTable = TableRegistry::get('Communities');
        $clients = $communitiesTable->getClients($communityId);
        $result = $surveysTable->sendInvitationEmails($recipients, $clients, $survey, $senderEmail, $senderName);

        if ($result) {
            $successEmails = $recipients;
        } else {
            $errorEmails = $recipients;
        }

        $this->setInvitationFlashMessages($successEmails, $redundantEmails, $errorEmails, $uninvApprovedEmails);
        $this->request->data = [];
    }

    public function setInvitationFlashMessages($successEmails, $redundantEmails, $errorEmails, $uninvApprovedEmails)
    {
        $seCount = count($successEmails);
        if ($seCount) {
            $list = $this->arrayToList($successEmails);
            $msg = 'Survey '.__n('invitation', 'invitations', $seCount).' sent to '.$list;
            $this->Flash->success($msg);
        }

        $reCount = count($redundantEmails);
        if ($reCount) {
            $list = $this->arrayToList($redundantEmails);
            $msg = $list.__n(' has', ' have', $reCount).' already received a survey invitation';
            $this->Flash->set($msg);
        }

        $eeCount = count($errorEmails);
        if ($eeCount) {
            $list = $this->arrayToList($errorEmails);
            $msg = 'There was an error inviting '.$list.'. Please try again or contact an administrator if you need assistance.';
            $this->Flash->error($msg);
        }

        $rieCount = count($uninvApprovedEmails);
        if ($rieCount) {
            $list = $this->arrayToList($uninvApprovedEmails);
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
