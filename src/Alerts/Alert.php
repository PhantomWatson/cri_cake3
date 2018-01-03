<?php
namespace App\Alerts;

use App\Model\Entity\Community;
use App\Model\Entity\User;
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
                'created >=' => new DateTime('- 2 days')
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
     * @param string $mailerMethod Name of mailer method for alert
     * @param array|null $data Metadata to include in queued job in addition to default data
     * @throws \Exception
     * @return \Cake\ORM\Entity Saved job entity
     */
    public static function enqueueEmail($recipient, $community, $mailerMethod, $data = null)
    {
        $data = $data + [
            'user' => [
                'email' => $recipient->email,
                'name' => $recipient->name
            ],
            'community' => [
                'id' => $community->id,
                'name' => $community->name
            ],
            'mailerMethod' => $mailerMethod
        ];

        /** @var QueuedJobsTable $queuedJobs */
        $queuedJobs = TableRegistry::get('Queue.QueuedJobs');

        return $queuedJobs->createJob(
            'AdminTaskEmail',
            $data,
            ['reference' => $recipient->email]
        );
    }
}
