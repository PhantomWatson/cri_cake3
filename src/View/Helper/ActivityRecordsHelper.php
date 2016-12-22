<?php
namespace App\View\Helper;

use App\Model\Entity\ActivityRecord;
use Cake\View\Helper;

class ActivityRecordsHelper extends Helper
{
    public $helpers = ['Html'];

    /**
     * Returns a human-readable description of an activity record
     *
     * @param ActivityRecord $activityRecord ActivityRecord entity
     * @return string
     */
    public function event($activityRecord)
    {
        switch ($activityRecord->event) {
            case 'Model.Community.afterAdd':
                return 'Community added';
        }

        return $activityRecord->event;
    }

    /**
     * Returns a string indicating what user was responsible for an action
     *
     * @param ActivityRecord $activityRecord ActivityRecord entity
     * @return string
     */
    public function user($activityRecord)
    {
        if ($activityRecord->has('user')) {
            $retval = $activityRecord->user->name;
            $role = $activityRecord->user->role;
            $retval .= ' <span class="role-' . $role . '">(' . $role . ')</span> ';

            return $retval;
        }

        return '';
    }

    /**
     * Outputs any supplementary details relevant to this record
     *
     * @param ActivityRecord $activityRecord ActivityRecord entity
     * @return string
     */
    public function details($activityRecord)
    {
        return '';
    }
}
