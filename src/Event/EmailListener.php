<?php
namespace App\Event;

use App\Alerts\Alert;
use App\Model\Entity\Community;
use App\Model\Table\CommunitiesTable;
use App\Model\Table\DeliverablesTable;
use App\Model\Table\DeliveriesTable;
use App\Model\Table\ProductsTable;
use App\Model\Table\SurveysTable;
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
            'Model.Purchase.afterAdminAdd' => 'sendDeliverOptPresentationEmail',
            'Model.Delivery.afterAdd' => 'sendSchedulePresentationEmail'
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
            $this->sendCreateSurveyEmail($event, $meta, $community);
        }

        if ($toStep == 4) {
            $this->sendDeliverPolicyDevEmail($event, $meta, $community);
        }
    }

    /**
     * Enqueues emails that prompt admins to create surveys
     *
     * @param Event $event Event
     * @param array $meta Metadata
     * @param Community $community Community entity
     * @return void
     * @throws \Exception
     */
    public function sendCreateSurveyEmail(Event $event, array $meta, Community $community)
    {
        /**
         * @var UsersTable $usersTable
         * @var SurveysTable $surveysTable
         */
        $usersTable = TableRegistry::get('Users');
        $surveysTable = TableRegistry::get('Surveys');
        $recipients = $usersTable->getAdminEmailRecipients('ICI');
        $toStep = $this->getToStep($meta);
        $newSurveyType = $toStep == 2 ? 'official' : 'organization';

        // Skip sending email if the new survey has already been created
        if ($surveysTable->hasBeenCreated($community->id, $newSurveyType)) {
            return;
        }

        $jobData = [
            'eventName' => $event->getName(),
            'community' => ['slug' => $community->slug],
            'newSurveyType' => $newSurveyType,
            'toStep' => $toStep
        ];
        foreach ($recipients as $recipient) {
            Alert::enqueueEmail($recipient, $community, $jobData);
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
        $jobData = [
            'eventName' => $eventName,
            'meta' => $meta
        ];
        foreach ($recipients as $recipient) {
            Alert::enqueueEmail($recipient, $community, $jobData);
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
            'Model.Purchase.afterAdminAdd' => 'CBER',
            'Model.Community.afterAutomaticAdvancement' => 'ICI',
            'Model.Community.afterScoreIncrease' => 'ICI',
            'Model.Delivery.afterAdd' => 'ICI'
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

    /**
     * Enqueues emails that alert admins to the need to schedule a presentation for a community
     *
     * @param \Cake\Event\Event $event Event
     * @param array $meta Array of metadata (communityId, etc.)
     * @return void
     * @throws \Exception
     */
    public function sendSchedulePresentationEmail(Event $event, array $meta = [])
    {
        // Skip if it's not a presentation that was delivered
        /** @var DeliverablesTable $deliverablesTable */
        $deliverablesTable = TableRegistry::get('Deliverables');
        if (!$deliverablesTable->isPresentation($meta['deliverableId'])) {
            return;
        }

        // Skip if this presentation has already been scheduled
        /** @var CommunitiesTable $communitiesTable */
        $communitiesTable = TableRegistry::get('Communities');
        $presentationLetter = $deliverablesTable->getPresentationLetter($meta['deliverableId']);
        if ($communitiesTable->presentationIsScheduled($meta['communityId'], $presentationLetter)) {
            return;
        }

        $this->sendAdminTaskEmail($event, $meta);
    }

    /**
     * Enqueues emails that prompt admins to deliver policy development materials
     *
     * @param Event $event Event
     * @param array $meta Metadata
     * @param Community $community Community entity
     * @return void
     * @throws \Exception
     */
    public function sendDeliverPolicyDevEmail(Event $event, array $meta, Community $community)
    {
        // Skip if already delivered
        /** @var DeliveriesTable $deliveriesTable */
        $deliveriesTable = TableRegistry::get('Deliveries');
        $isRecorded = $deliveriesTable->isRecorded($community->id, DeliverablesTable::POLICY_DEVELOPMENT);
        if ($isRecorded) {
            return;
        }

        // Send to both CBER and ICI
        /**
         * @var UsersTable $usersTable
         */
        $usersTable = TableRegistry::get('Users');
        $recipients = $usersTable->getAdminEmailRecipients('both');
        $jobData = [
            'eventName' => $event->getName(),
            'community' => ['slug' => $community->slug],
            'mailerMethod' => 'deliverPolicyDev'
        ];
        foreach ($recipients as $recipient) {
            Alert::enqueueEmail($recipient, $community, $jobData);
        }
    }
}
