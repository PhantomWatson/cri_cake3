<?php

namespace App\Event;

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
        return [
            'Model.Community.afterAdd' => 'recordActivity',
        ];
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
        $activityRecordsTable = TableRegistry::get('ActivityRecords');
        $activityRecordsTable->add($event->name(), $meta);
    }
}
