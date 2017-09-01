<?php
namespace App\Event;

use App\Model\Entity\Community;
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
            'Model.Community.afterScoreIncrease' => 'sendCommunityPromotedEmail'
        ];
    }

    /**
     * Sends emails informing clients that their community has been promoted
     *
     * @param \Cake\Event\Event $event Event
     * @param array $meta Array of metadata (communityId, etc.)
     * @return void
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
}
