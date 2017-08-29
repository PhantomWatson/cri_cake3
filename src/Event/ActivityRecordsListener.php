<?php

namespace App\Event;

use App\Model\Table\ActivityRecordsTable;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;

class ActivityRecordsListener implements EventListenerInterface
{

    /**
     * @var null|int The logged-in user's ID
     */
    public $userId = null;

    /**
     * implementedEvents() method
     *
     * @return array
     */
    public function implementedEvents()
    {
        $events = [
            'Model.Community.afterAdd',
            'Model.Community.afterDelete',
            'Model.Community.afterActivate',
            'Model.Community.afterDeactivate',
            'Model.User.afterAdd',
            'Model.User.afterDelete',
            'Model.Survey.afterLinked',
            'Model.Survey.afterLinkUpdated',
            'Model.Survey.afterActivate',
            'Model.Survey.afterDeactivate',
            'Model.Product.afterPurchase',
            'Model.Purchase.afterAdminAdd',
            'Model.Purchase.afterRefund',
            'Model.Response.afterImport',
            'Model.Community.afterScoreIncrease',
            'Model.Community.afterScoreDecrease',
            'Model.Respondent.afterUninvitedApprove',
            'Model.Respondent.afterUninvitedDismiss',
            'Model.Survey.afterInvitationsSent',
            'Model.Survey.afterRemindersSent',
            'Model.Delivery.afterAdd',
            'Model.Community.afterAddClient',
            'Model.Community.afterRemoveClient',
            'Model.Community.afterAutomaticAdvancement'
        ];

        return array_fill_keys($events, 'recordActivity');
    }

    /**
     * Gets or sets userId property
     *
     * @param int|null $userId User ID
     * @return int|null
     */
    public function userId($userId = null)
    {
        if ($userId === null) {
            return $this->userId;
        }
        $this->userId = $userId;

        return null;
    }

    /**
     * Passes the event name and metadata to ActivityRecordsTable::add()
     *
     * @param \Cake\Event\Event $event Event
     * @param array $meta Array of metadata (userId, communityId, etc.)
     * @return void
     */
    public function recordActivity(Event $event, array $meta = null)
    {
        if (! isset($meta['userId']) && $this->userId()) {
            $meta['userId'] = $this->userId();
        }

        /** @var ActivityRecordsTable $activityRecordsTable */
        $activityRecordsTable = TableRegistry::get('ActivityRecords');
        $activityRecordsTable->add($event->getName(), $meta);
    }
}
