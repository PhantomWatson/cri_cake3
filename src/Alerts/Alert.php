<?php
namespace App\Alerts;

use App\Model\Entity\Community;
use App\Model\Entity\User;
use App\Model\Table\UsersTable;
use Cake\ORM\TableRegistry;
use DateTime;
use Queue\Model\Table\QueuedJobsTable;

class Alert
{
    /**
     * Returns a boolean indicating whether or not a specific alert was sent in the last two days
     *
     * @param string $email Recipient email address
     * @param int $communityId Community ID
     * @param string $mailerMethod Name of mailer method for alert
     * @return bool
     */
    public static function isRecentlySent($email, $communityId, $mailerMethod)
    {
        $queuedJobsTable = TableRegistry::get('Queue.QueuedJobs');
        $recentEmails = $queuedJobsTable->find()
            ->select(['data'])
            ->where([
                'job_type' => 'AdminTaskEmail',
                'reference' => $email,
                'created >=' => new DateTime('-2 days')
            ])
            ->all();

        if ($recentEmails->isEmpty()) {
            return false;
        }

        foreach ($recentEmails as $recentEmail) {
            $data = unserialize($recentEmail['data']);
            if ($data['community']['id'] == $communityId && $data['mailerMethod'] == $mailerMethod) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User $recipient Alert recipients
     * @param Community $community Community entity
     * @param array|null $data Metadata to include in queued job in addition to default data
     * @throws \Exception
     * @return \Cake\ORM\Entity Saved job entity
     */
    public static function enqueueEmail($recipient, $community, $data = null)
    {
        $data['user']['email'] = $recipient->email;
        $data['user']['name'] = $recipient->name;
        $data['community']['id'] = $community->id;
        $data['community']['name'] = $community->name;

        /** @var QueuedJobsTable $queuedJobs */
        $queuedJobs = TableRegistry::get('Queue.QueuedJobs');

        return $queuedJobs->createJob(
            'AdminTaskEmail',
            $data,
            ['reference' => $recipient->email]
        );
    }

    /**
     * Sends alerts to all members of the CBER group, ICI group, or both
     *
     * @param string $adminGroupName 'CBER', 'ICI', or 'both'
     * @param array $meta Queued job metadata
     * @return void
     * @throws \Exception
     */
    public static function sendToGroup($adminGroupName, $meta)
    {
        /**
         * @var Community $community
         * @var UsersTable $usersTable
         */
        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($meta['communityId']);
        $usersTable = TableRegistry::get('Users');
        $recipients = $usersTable->getAdminEmailRecipients($adminGroupName);
        foreach ($recipients as $recipient) {
            self::enqueueEmail($recipient, $community, $meta);
        }
    }
}
