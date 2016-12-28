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
                    'afterDelete' => 'Community deleted',
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
                    'afterAdd' => 'User account added',
                    'afterDelete' => 'User account deleted'
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
        $name = '';
        $role = '';
        if ($activityRecord->has('user')) {
            $name = $activityRecord->user->name;
            $role = $activityRecord->user->role;
        } else {
            $meta = unserialize($activityRecord->meta);
            if (isset($meta['userName'])) {
                $name = $meta['userName'];
            }
            if (isset($meta['userRole'])) {
                $role = $meta['userRole'];
            }
        }

        $retval = $name;
        if ($role) {
            $retval .= ' <span class="role role-' . $role . '">' . $role . '</span>';
        }

        return $retval;
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
        $detailsFormats = [
            'Model' => [
                'Community' => [
                    'afterScoreDecrease' => 'From Step [previousScore] to Step [newScore]',
                    'afterScoreIncrease' => 'From Step [previousScore] to Step [newScore]'
                ],
                'Product' => [
                    'afterAdminAdd' => '[productName]',
                    'afterPurchase' => '[productName]',
                    'afterRefund' => '[productName]'
                ],
                'Survey' => [
                    'afterActivate' => 'Community [surveyType]s questionnaire',
                    'afterDeactivate' => 'Community [surveyType]s questionnaire',
                    'afterInvitationsSent' => '[invitedCount] invitation(s) to the community [surveyType]s questionnaire sent',
                    'afterLinked' => 'Community [surveyType]s questionnaire',
                    'afterLinkUpdated' => 'Community [surveyType]s questionnaire',
                    'afterRemindersSent' => '[remindedCount] reminder(s) to complete the community [surveyType]s questionnaire sent'
                ],
                'Respondent' => [
                    'afterUninvitedApprove' => '[respondentName], responding to community [surveyType]s questionnaire',
                    'afterUninvitedDismiss' => '[respondentName], responding to community [surveyType]s questionnaire'
                ],
                'Response' => [
                    'afterImport' => '[responseCount] response(s) to the community [surveyType]s questionnaire'
                ],
                'User' => [
                    'afterAdd' => '[userRole] account created for [userName]',
                    'afterDelete' => '[userName]\'s  [userRole] account deleted'
                ]
            ]
        ];

        $detailsFormat = Hash::extract($detailsFormats, $activityRecord->event);
        if ($detailsFormat) {
            return $this->getFormattedDetails($detailsFormat[0], $meta);
        }

        return null;
    }

    /**
     * Returns a string with meta var values swapped in for [varName]s
     *
     * @param string $detailsFormat String with [varName]s in place of values
     * @param array $meta This activity record's meta variables
     * @return string
     */
    private function getFormattedDetails($detailsFormat, $meta)
    {
        // Extract meta variables in details format string
        preg_match_all("/\[([^\]]*)\]/", $detailsFormat, $metaVarNames);
        $metaVarNames = $metaVarNames[1];

        // Swap meta var values in or return an error if a required var is not provided
        $retval = $detailsFormat;
        foreach ($metaVarNames as $varName) {
            if (! isset($meta[$varName])) {
                return 'Error getting activity details: Unknown ' . $varName;
            }
            $retval = str_replace("[$varName]", $meta[$varName], $retval);
        }

        return ucfirst($retval);
    }

    /**
     * Returns the name of the community associated with this activity record, or null if unspecified
     *
     * @param ActivityRecord $activityRecord Activity record entity
     * @return string|null
     */
    public function community($activityRecord)
    {
        // Pull community name from the database
        if ($activityRecord->has('community')) {
            return $activityRecord->community->name;
        }

        // Use stored community name in case community record is no longer in the database
        $meta = unserialize($activityRecord->meta);
        if (isset($meta['communityName'])) {
            return $meta['communityName'];
        }

        return null;
    }
}
