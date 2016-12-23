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
            case 'Model.User.afterAdd':
                return 'User account added';
            case 'Model.Survey.afterLinked':
                return 'Questionnaire linked to SurveyMonkey';
            case 'Model.Survey.afterLinkUpdated':
                return 'Questionnaire\'s link to SurveyMonkey updated';
            case 'Model.Survey.afterActivate':
                return 'Questionnaire activated';
            case 'Model.Survey.afterDeactivate':
                return 'Questionnaire deactivated';
            case 'Model.Product.afterPurchase':
                return 'Product purchased';
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
     * @return string|null
     */
    public function details($activityRecord)
    {
        $meta = unserialize($activityRecord->meta);
        switch ($activityRecord->event) {
            case 'Model.User.afterAdd':
                return ucwords($meta['userRole']). " account created for {$meta['userName']} ";
            case 'Model.Survey.afterLinked':
            case 'Model.Survey.afterLinkUpdated':
            case 'Model.Survey.afterActivate';
            case 'Model.Survey.afterDeactivate';
                return "Community {$meta['surveyType']}s questionnaire";
            case 'Model.Product.afterPurchase':
                return $meta['productName'];

        }

        return null;
    }
}
