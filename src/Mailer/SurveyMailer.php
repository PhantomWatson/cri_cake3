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
     * @return Email
     */
    public function reminders($surveyId, $sender)
    {
        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($surveyId);

        $communitiesTable = TableRegistry::get('Communities');
        $clients = $communitiesTable->getClients($survey->community_id);

        $respondentsTable = TableRegistry::get('Respondents');
        $recipients = $respondentsTable->getUnresponsive($surveyId);
        $recipients = Hash::extract($recipients, '{n}.email');

        $email = $this
            ->setTo(Configure::read('noreply_email'))
            ->setTemplate('survey_invitation')
            ->setViewVars([
                'clients' => $clients,
                'criUrl' => Router::url('/', true),
                'surveyType' => $survey->type,
                'surveyUrl' => $survey->sm_url
            ]);
        if ($sender['email']) {
            $email->setReplyTo($sender['email'], $sender['name']);
        }
        foreach ($recipients as $recipient) {
            $email->addBcc($recipient);
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
        $recipients = $params['recipients'];

        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($surveyId);

        $communitiesTable = TableRegistry::get('Communities');
        $clients = $communitiesTable->getClients($communityId);

        $email = $this
            ->setTemplate('survey_invitation')
            ->setTo(Configure::read('noreply_email'))
            ->setViewVars([
                'clients' => $clients,
                'criUrl' => Router::url('/', true),
                'surveyType' => $survey->type,
                'surveyUrl' => $survey->sm_url
            ]);
        if ($senderEmail) {
            $email->setReplyTo($senderEmail, $senderName);
        }
        foreach ($recipients as $recipient) {
            $email->addBcc($recipient);
        }

        return $email;
    }
}
