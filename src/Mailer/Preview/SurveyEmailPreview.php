<?php
declare(strict_types=1);

namespace App\Mailer\Preview;

use Cake\ORM\TableRegistry;
use DebugKit\Mailer\MailPreview;

class SurveyEmailPreview extends MailPreview
{
    /**
     * Preview method for SurveyMailer::reminder()
     *
     * @return \Cake\Mailer\Email
     */
    public function reminders()
    {
        $surveysTable = TableRegistry::getTableLocator()->get('Surveys');
        $survey = $surveysTable->find()->first();

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $sender = $usersTable->find()->first()->toArray();

        return $this->getMailer('Survey')
            ->reminders($survey->id, $sender);
    }

    /**
     * Preview method for SurveyMailer::invitations()
     *
     * @return \Cake\Mailer\Email
     */
    public function invitations()
    {
        $surveysTable = TableRegistry::getTableLocator()->get('Surveys');
        $survey = $surveysTable->find()->first();

        return $this->getMailer('Survey')
            ->invitations([
                'surveyId' => $survey->id,
                'communityId' => $survey->community_id,
                'senderEmail' => 'sender@example.com',
                'senderName' => 'Fake Sender',
                'recipients' => ['recipient@example.com'],
            ]);
    }
}
