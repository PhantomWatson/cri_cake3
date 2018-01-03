<?php
namespace App\Controller;

use App\Alerts\Alerts;
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
     * Skips over recently-created communities (within last two hours) to avoid sending unnecessary alerts to
     * administrators who are in the process of adding clients
     *
     * @return void
     * @throws \Exception
     */
    public function checkNoClientAssigned()
    {
        /**
         * @var CommunitiesTable $communitiesTable
         * @var UsersTable $usersTable
         */
        $communitiesTable = TableRegistry::get('Communities');
        $communities = $communitiesTable->find('noClientAssignedAlertable')->all();
        $sentEmails = [];
        $skippedEmails = [];

        if (!$communities->isEmpty()) {
            $usersTable = TableRegistry::get('Users');
            $recipients = $usersTable->getAdminEmailRecipients('ICI');
            foreach ($communities as $community) {
                foreach ($recipients as $recipient) {
                    $ifRecentlySent = Alerts::isRecentlySent(
                        $recipient->email,
                        $community->id,
                        'assignClient'
                    );
                    if ($ifRecentlySent) {
                        $skippedEmails[] = $recipient->email;
                        continue;
                    }
                    Alerts::enqueueEmail($recipient, $community, 'assignClient');
                    $sentEmails[] = $recipient->email;
                }
            }
        }

        $this->set(compact(
            'communities',
            'sentEmails',
            'skippedEmails'
        ));
        $this->viewBuilder()->setLayout('ajax');
    }
}
