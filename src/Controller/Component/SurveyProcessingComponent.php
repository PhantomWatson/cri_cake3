<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
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

        $invitees = empty($this->request->data('invitees')) ? [] : $this->request->data('invitees');
        foreach ($invitees as $i => $invitee) {
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
                $respondent->approved = true;

                // If name is provided, update name
                if ($invitee['name']) {
                    $respondent->name = $invitee['name'];
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
                'survey_id' => $surveyId,
                'community_id' => $communityId,
                'type' => $respondentType,
                'invited' => true,
                'approved' => 1
            ]);
            if ($respondentsTable->save($respondent)) {
                $surveysTable = TableRegistry::get('Surveys');
                if ($surveysTable->sendInvitationEmail($respondent->id)) {
                    $successEmails[] = $invitee['email'];
                    $allRespondents[] = $invitee['email'];
                    $approvedRespondents[] = $invitee['email'];
                } else {
                    $errorEmails[] = $invitee['email'];
                }
            } else {
                $errorEmails[] = $invitee['email'];
            }
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
}