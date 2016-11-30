<?php
namespace App\View\Helper;

use Cake\View\Helper;

class ClientHomeHelper extends Helper
{
    public $helpers = ['Html', 'Time'];

    /**
     * Returns a Bootstrap glyphicon indicating success or failure
     *
     * @param bool $bool Boolean for success or failure
     * @return string
     */
    public function glyphicon($bool) {
        $class = $bool ? 'ok' : 'remove';
        return '<span class="glyphicon glyphicon-'.$class.'"></span>';
    }

    /**
     * Returns a <tbody> string with the appropriate class
     *
     * @param $tbodyStep Step that this <tbody> contains
     * @param $currentStep Step that this community is currently at
     * @return string
     */
    public function tbodyForStep($tbodyStep, $currentStep) {
        if ($tbodyStep > floor($currentStep)) {
            return '<tbody class="future">';
        }
        if ($tbodyStep < floor($currentStep)) {
            return '<tbody class="past">';
        }
        return '<tbody class="current">';
    }

    /**
     * Outputs a table row
     *
     * @param string $icon Glyphicon indicating success or failure
     * @param string $description Description of task
     * @param string $actions Code for action buttons
     * @return string
     */
    public function row($icon, $description, $actions)
    {
        return "<tr><td>$icon</td><td>$description</td><td>$actions</td></tr>";
    }

    /**
     * "Purchased community officials survey" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function officialsSurveyPurchasedRow($params)
    {
        $description = $params['description'];
        $purchased = $params['purchased'];
        $purchaseUrl = $params['purchaseUrl'];
        $icon = $this->glyphicon($purchased);

        if ($purchased) {
            $actions = null;
        } else {
            $actions =
                '<a href="' . $purchaseUrl . '" class="btn btn-primary">' .
                    'Purchase Now' .
                '</a>';
        }

        return $this->row($icon, $description, $actions);
    }

    /**
     * "Survey is has been prepared" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function surveyReadyRow($params)
    {
        $description = $params['description'];
        $onCurrentStep = $params['onCurrentStep'];
        $surveyActive = $params['surveyActive'];
        $surveyComplete = $params['surveyComplete'];
        $surveyExists = $params['surveyExists'];
        $icon = $this->glyphicon($surveyExists);

        if ($onCurrentStep && ! $surveyActive && ! $surveyComplete) {
            $description .=
                '<p class="alert alert-info">' .
                'Your community\'s questionnaire is currently being prepared. Please check back later for updates.' .
                '</p>';
        }

        return $this->row($icon, $description, null);
    }

    /**
     * "Invite respondents" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function invitationRow($params)
    {
        $description = $params['description'];
        $invitationsSent = $params['invitationsSent'];
        $surveyActive = $params['surveyActive'];
        $icon = $this->glyphicon($invitationsSent);

        if ($surveyActive) {
            $buttonClass = 'btn btn-';
            $buttonClass .= ($invitationsSent ? 'default' : 'primary');
            $actions = $this->Html->link(
                'Send ' . ($invitationsSent ? 'More ' : '') . 'Invitations',
                [
                    'prefix' => 'client',
                    'controller' => 'Surveys',
                    'action' => 'invite',
                    'officials'
                ],
                ['class' => $buttonClass]
            );
        } else {
            $actions = null;
        }

        return $this->row($icon, $description, $actions);
    }

    /**
     * "Responses received / Import responses" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function responsesRow($params)
    {
        $autoImportFrequency = $params['autoImportFrequency'];
        $description = $params['description'];
        $importErrors = $params['importErrors'];
        $onCurrentStep = $params['onCurrentStep'];
        $responsesReceived = $params['responsesReceived'];
        $surveyActive = $params['surveyActive'];
        $surveyId = $params['surveyId'];
        $timeResponsesLastChecked = $params['timeResponsesLastChecked'];

        $icon = $this->glyphicon($responsesReceived);
        if ($onCurrentStep && $surveyActive) {
            $description .=
                '<button class="btn btn-link importing_note_toggler">' .
                    '<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>' .
                '</button>';
        }
        $description = '<p>' . $description . '</p>';

        if ($onCurrentStep && $surveyActive) {
            $description .=
                '<p class="importing_note" style="display: none;">' .
                    'Responses are automatically imported from SurveyMonkey' .
                    ($autoImportFrequency ? ' approximately '.$autoImportFrequency : '') .
                    ', but you can manually import them at any time.' .
                '</p>';
        }

        if ($timeResponsesLastChecked) {
            $description .=
                '<div class="last_import alert alert-info">' .
                    'New responses were last checked for ' .
                    $this->Time->timeAgoInWords($timeResponsesLastChecked, ['end' => '+1 year']) .
                '</div>';
        }

        if ($importErrors) {
            $description .= '<div class="import-results alert alert-danger">';
            $description .= __n('An error was', 'Errors were', count($importErrors));
            $description .= 'encountered the last time responses were imported:';
            $description .= '<ul>';
            foreach ($importErrors['official'] as $error) {
                $description .= "<li>$error</li>";
            }
            $description .= '</ul></div>';
        } else {
            $description .= '<div class="import-results"></div>';
        }

        $actions = '';
        if ($surveyActive) {
            $actions .=
                '<button class="btn btn-default btn-block import_button" data-survey-id="' . $surveyId . '">' .
                    'Import Responses' .
                '</button>';
            if ($responsesReceived) {
                $actions .= '<br />';
            }
        }
        if ($responsesReceived) {
            $actions .= $this->Html->link(
                'Review Responses',
                [
                    'prefix' => 'client',
                    'controller' => 'Respondents',
                    'action' => 'index',
                    'official'
                ],
                ['class' => 'btn btn-default']
            );
        }

        return $this->row($icon, $description, $actions);
    }

    /**
     * Response rate and reminders row
     *
     * @param array $params Parameters
     * @return string
     */
    public function responseRateRow($params)
    {
        $description = $params['description'];
        $responsesReceived = $params['responsesReceived'];
        $surveyActive = $params['surveyActive'];
        $thresholdReached = $params['thresholdReached'];

        $icon = $this->glyphicon($thresholdReached);

        if ($surveyActive && $responsesReceived) {
            $actions = $this->Html->link(
                'Reminders',
                [
                    'prefix' => 'client',
                    'controller' => 'Surveys',
                    'action' => 'remind',
                    'official'
                ],
                ['class' => 'btn btn-default']
            );
        } else {
            $actions = null;
        }

        return $this->row($icon, $description, $actions);
    }

    /**
     * "Addressing unapproved responses" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function unapprovedResponsesRow($params)
    {
        $allUnapprovedAddressed = $params['allUnapprovedAddressed'];
        $description = $params['description'];
        $hasUninvitedResponses = $params['hasUninvitedResponses'];

        $icon = $this->glyphicon($allUnapprovedAddressed);

        if ($hasUninvitedResponses) {
            $actions = $this->Html->link(
                'Approve / Dismiss',
                [
                    'prefix' => 'client',
                    'controller' => 'Respondents',
                    'action' => 'unapproved',
                    'official'
                ],
                ['class' => 'btn btn-' . ($allUnapprovedAddressed ? 'default' : 'primary')]
            );
        } else {
            $actions = null;
        }

        return $this->row($icon, $description, $actions);
    }

    /**
     * "Alignment has been calculated" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function alignmentCalculatedRow($params)
    {
        $description = $params['description'];
        $alignmentCalculated = $params['alignmentCalculated'];

        $icon = $this->glyphicon($alignmentCalculated);

        return $this->row($icon, $description, null);
    }

    /**
     * "Passed alignment assessment" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function alignmentResultRow($params)
    {
        $alignmentPassed = $params['alignmentPassed'];
        $description = $params['description'];

        $icon = $this->glyphicon($alignmentPassed);

        return $this->row($icon, $description, null);
    }

    /**
     * "Purchased community organizations survey" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function orgSurveyPurchasedRow($params)
    {
        $description = $params['description'];
        $purchased = $params['purchased'];
        $purchaseUrl = $params['purchaseUrl'];
        $icon = $this->glyphicon($purchased);

        if ($purchased) {
            $actions = null;
        } else {
            $actions =
                '<a href="' . $purchaseUrl . '" class="btn btn-primary">' .
                    'Purchase Now' .
                '</a>';
        }

        return $this->row($icon, $description, $actions);
    }

    public function policyDevPurchasedRow($params)
    {
        $description = $params['description'];
        $purchased = $params['purchased'];
        $purchaseUrl = $params['purchaseUrl'];

        $icon = $this->glyphicon($purchased);

        if ($purchased) {
            $actions = null;
        } else {
            $actions =
                '<a href="' . $purchaseUrl . '" class="btn btn-primary">' .
                    'Purchase Now' .
                '</a>';
        }

        return $this->row($icon, $description, $actions);
    }
}