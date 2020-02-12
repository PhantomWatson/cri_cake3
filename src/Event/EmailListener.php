<?php
declare(strict_types=1);

namespace App\Event;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;

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
        ];
    }

    /**
     * Sends emails informing clients that their community has been promoted
     *
     * @param \Cake\Event\Event $event Event
     * @param array $meta Array of metadata (communityId, etc.)
     * @return void
     * @throws \Cake\Network\Exception\InternalErrorException
     * @throws \Exception
     */
    public function sendCommunityPromotedEmail(Event $event, array $meta = [])
    {
        $communitiesTable = TableRegistry::get('Communities');
        $toStep = $this->getToStep($meta);

        /** @var \App\Model\Entity\Community $community */
        $community = $communitiesTable->find()
            ->select(['id', 'name', 'slug'])
            ->where(['id' => $meta['communityId']])
            ->contain([
                'Clients' => function ($q) {
                    /** @var \Cake\ORM\Query $q */

                    return $q->select(['id', 'name', 'email']);
                },
            ])
            ->first();

        /** @var \Queue\Model\Table\QueuedJobsTable $queuedJobs */
        $queuedJobs = TableRegistry::get('Queue.QueuedJobs');

        foreach ($community->clients as $client) {
            $queuedJobs->createJob(
                'CommunityPromotedEmail',
                [
                    'user' => [
                        'name' => $client->name,
                        'email' => $client->email,
                    ],
                    'community' => ['name' => $community->name],
                    'toStep' => $toStep,
                ],
                ['reference' => $client->email]
            );
        }
    }

    /**
     * Reads $meta and returns the step that a community was just promoted to
     *
     * @param array $meta Event metadata
     * @return int
     * @throws \Cake\Network\Exception\InternalErrorException
     */
    private function getToStep($meta)
    {
        if (isset($meta['toStep'])) {
            return $meta['toStep'];
        }

        if (isset($meta['newScore'])) {
            return $meta['newScore'];
        }

        throw new InternalErrorException('Step community was promoted to not specified');
    }
}
