<?php
namespace App\Event;

use App\Model\Entity\Community;
use App\Model\Table\CommunitiesTable;
use App\Model\Table\UsersTable;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Queue\Model\Table\QueuedJobsTable;

class EmailListener implements EventListenerInterface
{
    /**
     * implementedEvents() method
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Model.Community.afterAutomaticAdvancement' => 'sendCommunityPromotedEmail',
            'Model.Community.afterScoreIncrease' => 'sendCommunityPromotedEmail',
            'Model.Survey.afterDeactivate' => 'sendAdminTaskEmail',
        ];
    }

    /**
     * Sends emails informing clients that their community has been promoted
     *
     * @param \Cake\Event\Event $event Event
     * @param array $meta Array of metadata (communityId, etc.)
     * @return void
     * @throws InternalErrorException
     * @throws \Exception
     */
    public function sendCommunityPromotedEmail(Event $event, array $meta = [])
    {
        $communitiesTable = TableRegistry::get('Communities');
        if (isset($meta['toStep'])) {
            $toStep = $meta['toStep'];
        } elseif (isset($meta['newScore'])) {
            $toStep = $meta['newScore'];
        } else {
            throw new InternalErrorException('Step community was promoted to not specified');
        }

        /** @var Community $community */
        $community = $communitiesTable->find()
            ->select(['id', 'name'])
            ->where(['id' => $meta['communityId']])
            ->contain([
                'Clients' => function ($q) {
                    /** @var Query $q */

                    return $q->select(['id', 'name', 'email']);
                }
            ])
            ->first();

        /** @var QueuedJobsTable $queuedJobs */
        $queuedJobs = TableRegistry::get('Queue.QueuedJobs');

        foreach ($community->clients as $client) {
            $queuedJobs->createJob(
                'CommunityPromotedEmail',
                [
                    'user' => [
                        'name' => $client->name,
                        'email' => $client->email
                    ],
                    'community' => [
                        'name' => $community->name
                    ],
                    'toStep' => $toStep
                ],
                ['reference' => $client->email]
            );
        }
    }

    /**
     * Sends emails about admin tasks to users who have opted in
     *
     * @param \Cake\Event\Event $event Event
     * @param array $meta Array of metadata (communityId, etc.)
     * @return void
     * @throws \Exception
     */
    public function sendAdminTaskEmail(Event $event, array $meta = [])
    {
        /**
         * @var UsersTable $usersTable
         * @var CommunitiesTable $communitiesTable
         */
        $usersTable = TableRegistry::get('Users');
        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($meta['communityId']);
        $eventName = $event->getName();
        $adminGroup = $this->getAdminGroup($eventName);
        $recipients = $usersTable->getAdminEmailRecipients($adminGroup);

        /** @var QueuedJobsTable $queuedJobs */
        $queuedJobs = TableRegistry::get('Queue.QueuedJobs');
        foreach ($recipients as $recipient) {
            $queuedJobs->createJob(
                'AdminTaskEmail',
                [
                    'user' => [
                        'email' => $recipient->email,
                        'name' => $recipient->name
                    ],
                    'eventName' => $eventName,
                    'community' => [
                        'id' => $community->id,
                        'name' => $community->name,
                    ],
                    'meta' => $meta
                ],
                ['reference' => $recipient->email]
            );
        }
    }

    /**
     * Returns the name of the admin group that should receive an admin task email in response to the specified event
     *
     * @param string $eventName Event name
     * @return string
     */
    private function getAdminGroup($eventName)
    {
        $adminGroups = [
            'Model.Survey.afterDeactivate' => 'CBER'
        ];

        if (array_key_exists($eventName, $adminGroups)) {
            return $adminGroups[$eventName];
        }

        throw new InternalErrorException('Unrecognized event name: ' . $eventName);
    }
}
