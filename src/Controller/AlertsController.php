<?php
namespace App\Controller;

use App\Model\Table\CommunitiesTable;
use App\Model\Table\UsersTable;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use DateTime;
use Queue\Model\Table\QueuedJobsTable;

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
         * @var UsersTable $usersTable
         * @var QueuedJobsTable $queuedJobs
         * @var CommunitiesTable $communitiesTable
         */
        $communitiesTable = TableRegistry::get('Communities');
        $communities = $communitiesTable->find()
            ->select(['id', 'name'])
            ->where([
                'Communities.active' => true,
                'Communities.created <=' => new DateTime('-2 hours')
            ])
            ->matching('OfficialSurvey')
            ->notMatching('Clients')
            ->all();

        if ($communities->isEmpty()) {
            $message = 'No communities with officials surveys have been added more than two hours ago ' .
                'that lack clients.';
        } else {
            $communityNames = Hash::extract($communities->toArray(), '{n}.name');
            $message = 'These communities with officials surveys have been added more than two hours ago ' .
                'and lack clients: ';
            $message .= implode(', ', $communityNames) . '.';

            $usersTable = TableRegistry::get('Users');
            $recipients = $usersTable->getAdminEmailRecipients('ICI');
            $queuedJobs = TableRegistry::get('Queue.QueuedJobs');
            $sentEmails = [];
            $skippedEmails = [];
            foreach ($communities as $community) {
                foreach ($recipients as $recipient) {
                    // Avoid sending an email if one was sent < 2 days ago
                    $skipSending = false;
                    $recentEmails = $queuedJobs->find()
                        ->select(['data'])
                        ->where([
                            'job_type' => 'AdminTaskEmail',
                            'reference' => $recipient->email,
                            'created >=' => new DateTime('- 2 days')
                        ])
                        ->all();
                    if (!$recentEmails->isEmpty()) {
                        foreach ($recentEmails as $recentEmail) {
                            $data = unserialize($recentEmail['data']);
                            if ($data['community']['id'] == $community->id && $data['mailerMethod'] == 'assignClient') {
                                $skipSending = true;
                                $skippedEmails[] = $recipient->email;
                                break;
                            }
                        }
                    }

                    if ($skipSending) {
                        continue;
                    }

                    $queuedJobs->createJob(
                        'AdminTaskEmail',
                        [
                            'user' => [
                                'email' => $recipient->email,
                                'name' => $recipient->name
                            ],
                            'community' => [
                                'id' => $community->id,
                                'name' => $community->name
                            ],
                            'mailerMethod' => 'assignClient'
                        ],
                        ['reference' => $recipient->email]
                    );
                    $sentEmails[] = $recipient->email;
                }
            }

            if ($sentEmails) {
                $message .= sprintf(
                    ' Alert %s sent to %s.',
                    __n('email', 'emails', count($sentEmails)),
                    implode($sentEmails)
                );
            }

            if ($skippedEmails) {
                $message .= sprintf(
                    ' Skipping sending %s to %s (%s alerted < 2 hours ago).',
                    __n('email', 'emails', count($skippedEmails)),
                    implode(', ', $skippedEmails),
                    __n('was', 'were', count($skippedEmails))
                );
            }
        }

        $this->set('message', $message);
        $this->viewBuilder()->setLayout('ajax');
    }
}
