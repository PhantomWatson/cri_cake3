<?php
namespace App\Event;

use App\Model\Entity\Community;
use App\Model\Table\CommunitiesTable;
use App\Model\Table\ProductsTable;
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
            'Model.Product.afterPurchase' => 'sendDeliverOptPresentationEmail',
            'Model.Purchase.afterAdminAdd' => 'sendDeliverOptPresentationEmail'
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
        $toStep = $this->getToStep($meta);

        /** @var Community $community */
        $community = $communitiesTable->find()
            ->select(['id', 'name', 'slug'])
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
                    'community' => ['name' => $community->name],
                    'toStep' => $toStep
                ],
                ['reference' => $client->email]
            );
        }

        // Send "time to create a survey" email to admins
        if (in_array($toStep, [2, 3])) {
            /**
             * @var UsersTable $usersTable
             * @var QueuedJobsTable $queuedJobs
             */
            $usersTable = TableRegistry::get('Users');
            $queuedJobs = TableRegistry::get('Queue.QueuedJobs');
            $recipients = $usersTable->getAdminEmailRecipients('ICI');
            foreach ($recipients as $recipient) {
                $queuedJobs->createJob(
                    'AdminTaskEmail',
                    [
                        'user' => [
                            'email' => $recipient->email,
                            'name' => $recipient->name
                        ],
                        'eventName' => $event->getName(),
                        'community' => [
                            'id' => $community->id,
                            'name' => $community->name,
                            'slug' => $community->slug
                        ],
                        'newSurveyType' => $toStep == 2 ? 'official' : 'organization',
                        'toStep' => $toStep
                    ],
                    ['reference' => $recipient->email]
                );
            }
        }
    }

    /**
     * Enqueues emails about admin tasks to users who have opted in
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
     * Enqueues emails that alert admins to the need to deliver optional presentation materials
     *
     * @param \Cake\Event\Event $event Event
     * @param array $meta Array of metadata (communityId, etc.)
     * @return void
     * @throws \Exception
     */
    public function sendDeliverOptPresentationEmail(Event $event, array $meta = [])
    {
        /** @var ProductsTable $productsTable */
        $productsTable = TableRegistry::get('Products');
        $presentationLetter = $productsTable->getPresentationLetter($meta['productId']);
        if (in_array($presentationLetter, ['a', 'b'])) {
            $this->sendAdminTaskEmail($event, $meta);
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
            'Model.Survey.afterDeactivate' => 'CBER',
            'Model.Product.afterPurchase' => 'CBER',
            'Model.Purchase.afterAdminAdd' => 'CBER'
        ];

        if (array_key_exists($eventName, $adminGroups)) {
            return $adminGroups[$eventName];
        }

        throw new InternalErrorException('Unrecognized event name: ' . $eventName);
    }

    /**
     * Reads $meta and returns the step that a community was just promoted to
     *
     * @param array $meta Event metadata
     * @return int
     * @throws InternalErrorException
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
