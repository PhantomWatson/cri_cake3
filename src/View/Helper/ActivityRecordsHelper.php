<?php
namespace App\View\Helper;

use App\Model\Entity\ActivityRecord;
use Cake\Utility\Hash;
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
        $eventDescriptions = [
            'Model' => [
                'Community' => [
                    'afterAdd' => 'Community added',
                    'afterScoreDecrease' => 'Community demoted',
                    'afterScoreIncrease' => 'Community promoted',
                ],
                'Product' => [
                    'afterPurchase' => 'Product purchased'
                ],
                'Purchase' => [
                    'afterAdminAdd' => 'Payment record added',
                    'afterRefund' => 'Refund recorded',
                ],
                'Respondent' => [
                    'afterUninvitedApprove' => 'Uninvited respondent approved',
                    'afterUninvitedDismiss' => 'Uninvited respondent dismissed',
                ],
                'Response' => [
                    'afterImport' => 'Responses imported'
                ],
                'Survey' => [
                    'afterActivate' => 'Questionnaire activated',
                    'afterDeactivate' => 'Questionnaire deactivated',
                    'afterInvitationsSent' => 'Questionnaire invitations sent',
                    'afterLinked' => 'Questionnaire linked to SurveyMonkey',
                    'afterLinkUpdated' => 'Questionnaire\'s link to SurveyMonkey updated',
                    'afterRemindersSent' => 'Questionnaire reminders sent',
                ],
                'User' => [
                    'afterAdd' => 'User account added'
                ]
            ]
        ];
        $eventDescription = Hash::extract($eventDescriptions, $activityRecord->event);

        return $eventDescription ? $eventDescription[0] : $activityRecord->event;
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
            $retval .= ' <span class="role role-' . $role . '">' . $role . '</span> ';

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
            case 'Model.Respondent.afterUninvitedApprove':
            case 'Model.Respondent.afterUninvitedDismiss':
                return "{$meta['respondentName']}, responding to community {$meta['surveyType']}s questionnaire";
            case 'Model.Survey.afterInvitationsSent':
                return $meta['invitedCount'] .
                    __n(' invitations', ' invitation', count($meta['invitedCount'])) .
                    " to the community {$meta['surveyType']}s questionnaire sent";
            case 'Model.Survey.afterRemindersSent':
                return $meta['remindedCount'] .
                    __n(' reminders', ' reminder', count($meta['remindedCount'])) .
                    " to complete the community {$meta['surveyType']}s questionnaire sent";
        }

        return null;
    }
}
