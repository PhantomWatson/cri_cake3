<?php
namespace App\View\Helper;

use Cake\View\Helper;

class ClientHomeHelper extends Helper
{
    public $helpers = ['Html'];

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
     * "Survey is has been prepared" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function surveyReadyRow($params)
    {
        $surveyExists = $params['surveyExists'];
        $surveyActive = $params['surveyActive'];
        $surveyComplete = $params['surveyComplete'];
        $description = $params['description'];
        $onCurrentStep = $params['onCurrentStep'];

        $icon = $this->glyphicon($surveyExists);
        if ($onCurrentStep && ! $surveyActive && ! $surveyComplete) {
            $description .=
                '<p class="alert alert-info">' .
                'Your community\'s questionnaire is currently being prepared. Please check back later for updates.' .
                '</p>';
        }

        return $this->row($icon, $description, null);
    }

    public function invitationRow($params)
    {
        $invitationsSent = $params['invitationsSent'];
        $description = $params['description'];
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
}