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
            case 'Model.Purchase.afterAdminAdd':
                return 'Payment record added';
            case 'Model.Purchase.afterRefund':
                return 'Refund recorded';
            case 'Model.Response.afterImport':
                return 'Responses imported';
            case 'Model.Community.afterScoreIncrease':
                return 'Community promoted';
            case 'Model.Community.afterScoreDecrease':
                return 'Community demoted';
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
                return ucwords($meta['userRole']) . " account created for {$meta['userName']}";
            case 'Model.Survey.afterLinked':
            case 'Model.Survey.afterLinkUpdated':
            case 'Model.Survey.afterActivate':
            case 'Model.Survey.afterDeactivate':
                return "Community {$meta['surveyType']}s questionnaire";
            case 'Model.Product.afterPurchase':
            case 'Model.Purchase.afterAdminAdd':
            case 'Model.Purchase.afterRefund':
                return $meta['productName'];
            case 'Model.Response.afterImport':
                return $meta['responseCount'] .
                    __n(' responses', ' response', count($meta['responseCount'])) .
                    " to the community {$meta['surveyType']}s questionnaire";
            case 'Model.Community.afterScoreIncrease':
            case 'Model.Community.afterScoreDecrease':
                return "From Step {$meta['previousScore']} to Step {$meta['newScore']}";
        }

        return null;
    }
}
