<?php
namespace App\Mailer;

use Cake\Core\Configure;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Hash;

class Mailer
{
    /**
     *
     *
     * @param int $surveyId
     * @param array $sender
     * @return boolean
     */
    public function sendReminders($surveyId, $sender)
    {
        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($surveyId);

        $communitiesTable = TableRegistry::get('Communities');
        $clients = $communitiesTable->getClients($survey->community_id);

        $email = new Email('survey_invitation');
        $email->to(Configure::read('noreply_email'));

        if ($sender['email']) {
            $email->replyTo($sender['email'], $sender['name']);
            $email->returnPath($sender['email'], $sender['name']);
        }

        $respondentsTable = TableRegistry::get('Respondents');
        $recipients = $respondentsTable->getUnresponsive($surveyId);
        $recipients = Hash::extract($recipients, '{n}.email');
        foreach ($recipients as $recipient) {
            $email->addBcc($recipient);
        }

        $email->viewVars([
            'clients' => $clients,
            'criUrl' => Router::url('/', true),
            'surveyType' => $survey->type,
            'surveyUrl' => $survey->sm_url
        ]);

        if ($email->send()) {
            $survey->reminder_sent = date('Y-m-d H:i:s');
            return $surveysTable->save($survey);
        }

        return false;
    }
}
