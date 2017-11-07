<?php
namespace App\Mailer;

use Cake\Core\Configure;
use Cake\Mailer\Email;
use Cake\Mailer\Mailer;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Hash;

class SurveyMailer extends Mailer
{
    /**
     * Defines a reminder email to users who have already been invited nad have not yet responded
     *
     * @param int $surveyId Survey ID
     * @param array $sender User who is sending the email
     * @param string $recipient Recipient email address
     * @return Email
     */
    public function reminders($surveyId, $sender, $recipient)
    {
        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($surveyId);

        $communitiesTable = TableRegistry::get('Communities');
        $clients = $communitiesTable->getClients($survey->community_id);

        $email = $this
            ->setTo($recipient)
            ->setTemplate('survey_invitation')
            ->setSubject('Invitation to participate in Community Readiness Initiative questionnaire')
            ->setViewVars([
                'clients' => $clients,
                'criUrl' => Router::url('/', true),
                'surveyType' => $survey->type,
                'surveyUrl' => $survey->sm_url
            ])
            ->setDomain('cri.cberdata.org');
        if ($sender['email']) {
            $email->setReplyTo($sender['email'], $sender['name']);
        }

        return $email;
    }

    /**
     * Sends survey invitations
     *
     * @param array $params [surveyId, communityId, senderEmail, senderName, recipients]
     * @return Email
     */
    public function invitations($params)
    {
        $surveyId = $params['surveyId'];
        $communityId = $params['communityId'];
        $senderEmail = $params['senderEmail'];
        $senderName = $params['senderName'];
        $recipient = $params['recipient'];

        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($surveyId);

        $communitiesTable = TableRegistry::get('Communities');
        $clients = $communitiesTable->getClients($communityId);

        $email = $this
            ->setTemplate('survey_invitation')
            ->setTo($recipient)
            ->setSubject('Invitation to participate in Community Readiness Initiative questionnaire')
            ->setViewVars([
                'clients' => $clients,
                'criUrl' => Router::url('/', true),
                'surveyType' => $survey->type,
                'surveyUrl' => $survey->sm_url
            ])
            ->setDomain('cri.cberdata.org');
        if ($senderEmail) {
            $email->setReplyTo($senderEmail, $senderName);
        }

        return $email;
    }
}
