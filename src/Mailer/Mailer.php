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
     * @return bool
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

    /**
     * Sends survey invitations
     *
     * @param $params [surveyId, communityId, senderEmail, senderName, recipients]
     * @return array
     */
    public function sendInvitations($params)
    {
        extract($params);

        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($surveyId);

        $communitiesTable = TableRegistry::get('Communities');
        $clients = $communitiesTable->getClients($communityId);

        $email = new Email('survey_invitation');
        $email->to(Configure::read('noreply_email'));

        if ($senderEmail) {
            $email->replyTo($senderEmail, $senderName);
            $email->returnPath($senderEmail, $senderName);
        }

        foreach ($recipients as $recipient) {
            $email->addBcc($recipient);
        }

        $email->viewVars([
            'clients' => $clients,
            'criUrl' => Router::url('/', true),
            'surveyType' => $survey->type,
            'surveyUrl' => $survey->sm_url
        ]);

        return $email->send();
    }

    public function sendNewAccountEmail($user, $password, $senderEmail, $senderName)
    {
        $homeUrl = Router::url('/', true);
        $loginUrl = Router::url([
            'prefix' => false,
            'controller' => 'Users',
            'action' => 'login'
        ], true);
        $email = new Email('new_account');
        $email->to($user->email);
        if ($senderEmail) {
            $email->returnPath($senderEmail, $senderName);
        }
        $email->viewVars(compact(
            'user',
            'homeUrl',
            'loginUrl',
            'password'
        ));
        return $email->send();
    }

    /**
     * Sends an email with a link that can be used in the next
     * 24 hours to give the user access to /users/resetPassword
     *
     * @param int $userId
     * @return array
     */
    public function sendPasswordResetEmail($userId)
    {
        $timestamp = time();
        $usersTable = TableRegistry::get('Users');
        $hash = $usersTable->getPasswordResetHash($userId, $timestamp);
        $resetUrl = Router::url([
            'prefix' => false,
            'controller' => 'Users',
            'action' => 'resetPassword',
            $userId,
            $timestamp,
            $hash
        ], true);
        $email = new Email('reset_password');
        $user = $usersTable->get($userId);
        $email->to($user->email);
        $email->viewVars(compact(
            'user',
            'resetUrl'
        ));
        return $email->send();
    }
}
