<?php
namespace App\Mailer;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Hash;

class Mailer
{
    /**
     * Sends reminder emails to users who have already been invited nad have not yet responded
     *
     * @param int $surveyId Survey ID
     * @param array $sender User who is sending the email
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
            // Dispatch event
            $event = new Event('Model.Survey.afterRemindersSent', $this, ['meta' => [
                'communityId' => $survey->community_id,
                'surveyId' => $surveyId,
                'surveyType' => $survey->type,
                'remindedCount' => count($recipients)
            ]]);
            EventManager::instance()->dispatch($event);

            $survey->reminder_sent = date('Y-m-d H:i:s');

            return $surveysTable->save($survey);
        }

        return false;
    }

    /**
     * Sends survey invitations
     *
     * @param array $params [surveyId, communityId, senderEmail, senderName, recipients]
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

    /**
     * Sends an email informing a user that their account has been created
     *
     * @param User $user User
     * @param string $password Unhashed password
     * @return array
     */
    public function sendNewAccountEmail($user, $password)
    {
        $homeUrl = Router::url('/', true);
        $loginUrl = Router::url([
            'prefix' => false,
            'controller' => 'Users',
            'action' => 'login'
        ], true);
        $email = new Email('new_account');
        $email->to($user->email);
        $role = $user->role;
        $email->viewVars(compact(
            'homeUrl',
            'loginUrl',
            'password',
            'role',
            'user'
        ));

        return $email->send();
    }

    /**
     * Sends an email with a link that can be used in the next
     * 24 hours to give the user access to /users/resetPassword
     *
     * @param int $userId User ID
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

    /**
     * Sends a test email
     *
     * @param string $recipient Email address of recipient
     * @return array
     */
    public function sendTest($recipient)
    {
        $email = new Email();
        $email->to($recipient);
        $email->subject('CRI: Test email');
        $email->template('test');

        return $email->send();
    }
}
