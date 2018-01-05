<?php
namespace App\Controller;

use App\Alerts\Alert;
use App\Model\Table\CommunitiesTable;
use App\Model\Table\UsersTable;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\TableRegistry;

/**
 * This controller's actions check for conditions that warrant sending
 * emails to administrators and are triggered by cron jobs
 */
class AlertsController extends AppController
{
    use MailerAwareTrait;

    /**
     * initialize method
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow();
    }

    /**
     * Checks to see if any communities with officials surveys lack clients and sends alerts to administrators
     *
     * @return void
     * @throws \Exception
     */
    public function checkNoClientAssigned()
    {
        $this->sendAlerts('noClientAssignedAlertable', 'ICI', 'assignClient');
    }

    /**
     * Checks to see if any communities lack officials surveys and sends alerts to administrators
     *
     * @return void
     * @throws \Exception
     */
    public function checkNoOfficialsSurvey()
    {
        $this->sendAlerts('noOfficialsSurveyAlertable', 'ICI', 'createSurveyNewCommunity');
    }

    /**
     * Checks to see if any communities lack officials surveys and sends alerts to administrators
     *
     * @return void
     * @throws \Exception
     */
    public function checkSurveyNotActivated()
    {
        $adminGroup = 'ICI';
        $mailerMethod = 'activateSurvey';

        /**
         * @var CommunitiesTable $communitiesTable
         * @var UsersTable $usersTable
         */
        $communitiesTable = TableRegistry::get('Communities');
        $usersTable = TableRegistry::get('Users');
        $recipients = $usersTable->getAdminEmailRecipients($adminGroup);
        $sentEmails = [];
        $skippedEmails = [];
        foreach (['official', 'organization'] as $surveyType) {
            $communities = $communitiesTable
                ->find('surveyInactiveAlertable', ['surveyType' => $surveyType])
                ->all();

            if (!$communities->isEmpty()) {
                foreach ($communities as $community) {
                    foreach ($recipients as $recipient) {
                        $wasRecentlySent = Alert::isRecentlySent(
                            $recipient->email,
                            $community->id,
                            $mailerMethod
                        );
                        if ($wasRecentlySent) {
                            $skippedEmails[] = $recipient->email;
                            continue;
                        }
                        Alert::enqueueEmail($recipient, $community, [
                            'mailerMethod' => $mailerMethod,
                            'community' => ['slug' => $community->slug],
                            'surveyType' => $surveyType
                        ]);
                        $sentEmails[] = $recipient->email;
                    }
                }
            }
        }

        $skippedEmails = array_unique($skippedEmails);
        $sentEmails = array_unique($sentEmails);

        $this->set(compact(
            'communities',
            'sentEmails',
            'skippedEmails'
        ));
        $this->viewBuilder()->setLayout('ajax');
    }

    /**
     * Searches for alertable communities and sends alerts, avoiding sending alerts too frequently
     *
     * @param string $communityFinder Parameter for find()
     * @param string $adminGroup 'CBER', 'ICI', or 'both'
     * @param string $mailerMethod Name of AdminTaskMailer method
     * @return void
     * @throws \Exception
     */
    private function sendAlerts($communityFinder, $adminGroup, $mailerMethod)
    {
        /**
         * @var CommunitiesTable $communitiesTable
         * @var UsersTable $usersTable
         */
        $communitiesTable = TableRegistry::get('Communities');
        $communities = $communitiesTable->find($communityFinder)->all();
        $sentEmails = [];
        $skippedEmails = [];

        if (!$communities->isEmpty()) {
            $usersTable = TableRegistry::get('Users');
            $recipients = $usersTable->getAdminEmailRecipients($adminGroup);
            foreach ($communities as $community) {
                foreach ($recipients as $recipient) {
                    $wasRecentlySent = Alert::isRecentlySent(
                        $recipient->email,
                        $community->id,
                        $mailerMethod
                    );
                    if ($wasRecentlySent) {
                        $skippedEmails[] = $recipient->email;
                        continue;
                    }
                    Alert::enqueueEmail($recipient, $community, [
                        'mailerMethod' => $mailerMethod,
                        'community' => ['slug' => $community->slug]
                    ]);
                    $sentEmails[] = $recipient->email;
                }
            }
        }

        $skippedEmails = array_unique($skippedEmails);
        $sentEmails = array_unique($sentEmails);

        $this->set(compact(
            'communities',
            'sentEmails',
            'skippedEmails'
        ));
        $this->viewBuilder()->setLayout('ajax');
    }
}
