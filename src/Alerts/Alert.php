<?php
namespace App\Alerts;

use App\Model\Entity\Community;
use App\Model\Entity\User;
use App\Model\Table\UsersTable;
use Cake\ORM\TableRegistry;
use DateTime;
use Queue\Model\Table\QueuedJobsTable;
use ReflectionClass;

class Alert
{
    /**
     * Returns a boolean indicating whether or not a specific alert was sent in the last two days
     *
     * @param string $email Recipient email address
     * @param int $communityId Community ID
     * @param string $mailerMethod Name of mailer method for alert
     * @param string $surveyType Either 'official' or 'organization' (or null)
     * @return bool
     */
    public static function isRecentlySent($email, $communityId, $mailerMethod, $surveyType = null)
    {
        $queuedJobsTable = TableRegistry::get('Queue.QueuedJobs');
        $recentEmails = $queuedJobsTable->find()
            ->select(['data'])
            ->where([
                'job_type' => 'AdminAlertEmail',
                'reference' => $email,
                'created >=' => new DateTime('-2 days')
            ])
            ->all();

        if ($recentEmails->isEmpty()) {
            return false;
        }

        foreach ($recentEmails as $recentEmail) {
            $data = unserialize($recentEmail['data']);
            $isMatch = isset($data['community']['id']) &&
                $data['community']['id'] == $communityId &&
                isset($data['mailerMethod']) &&
                $data['mailerMethod'] == $mailerMethod;
            if ($surveyType) {
                $isMatch = $isMatch &&
                    isset($data['surveyType']) &&
                    $data['surveyType'] == $surveyType;
            }
            if ($isMatch) {
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
            'AdminAlertEmail',
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

    /**
     * Returns an array with each key being an alertable community name and each value being an array of applicable
     * alert method names
     *
     * @return array
     */
    public static function getAlertableCommunities()
    {
        // Get active communities
        $communitiesTable = TableRegistry::get('Communities');
        $communities = $communitiesTable
            ->find('list')
            ->where(['active' => true])
            ->orderAsc('name');

        // Get list of Alertable methods
        $class = new ReflectionClass('App\Alerts\Alertable');
        $alertableMethods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Remove __construct() from list of methods
        array_shift($alertableMethods);

        $alertableCommunities = [];
        foreach ($communities as $communityId => $communityName) {
            $alertable = new Alertable($communityId);
            $applicableAlerts = [];
            foreach ($alertableMethods as $alertableMethod) {
                if ($alertable->{$alertableMethod->name}()) {
                    $applicableAlerts[] = $alertableMethod->name;
                }
            }
            if ($applicableAlerts) {
                $alertableCommunities[] = [
                    'id' => $communityId,
                    'name' => $communityName,
                    'alerts' => $applicableAlerts
                ];
            }
        }

        return $alertableCommunities;
    }
}
