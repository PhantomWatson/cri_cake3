<?php
namespace App\View\Helper;

use App\Model\Table\ProductsTable;
use Cake\Network\Exception\InternalErrorException;
use Cake\Routing\Router;
use Cake\View\Helper;

class ClientHomeHelper extends Helper
{
    public $helpers = ['Html', 'Time'];
    public $userRole = null;

    /**
     * Sets the userRole property
     *
     * @param string $role Either 'client' or 'admin'
     * @return void
     */
    public function setUserRole($role)
    {
        $this->userRole = $role;
    }

    /**
     * Returns the non-null userRole property or throws an exception
     *
     * @return string
     * @throws InternalErrorException
     */
    private function getUserRole()
    {
        if (! $this->userRole) {
            throw new InternalErrorException('User role not set');
        }

        return $this->userRole;
    }

    /**
     * Returns a Bootstrap glyphicon indicating success or failure
     *
     * @param bool $bool Boolean for success or failure
     * @return string
     */
    public function glyphicon($bool)
    {
        $class = $bool ? 'ok' : 'remove';

        return '<span class="glyphicon glyphicon-' . $class . '"></span>';
    }

    /**
     * Returns a <tbody> string with the appropriate class
     *
     * @param int $tbodyStep Step that this <tbody> contains
     * @param int $currentStep Step that this community is currently at
     * @return string
     */
    public function tbodyForStep($tbodyStep, $currentStep)
    {
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
        $icon = $this->glyphicon($params['purchased']);

        if ($params['purchased'] || $params['optedOut']) {
            $actions = null;
        } else {
            $actions =
                '<a href="' . $params['purchaseUrl'] . '" class="btn btn-primary">' .
                    'Purchase Now' .
                '</a>' .
                $this->optOutLink(ProductsTable::OFFICIALS_SURVEY, $params['communityId']);
        }

        return $this->row($icon, $params['description'], $actions);
    }

    /**
     * "Step two not ready yet" row
     *
     * If the client has purchased step two but is still in step one,
     * this row explains that their survey is being prepared and (hopefully)
     * reassures them that no immediate action is needed on their part.
     *
     * @param array $params Parameters
     * @return null|string
     */
    public function stepTwoPendingRow($params)
    {
        if ($params['surveyPurchased'] && $params['onStepOne']) {
            $icon = $this->glyphicon(false);
            $description =
                '<p>' .
                    'Your community\'s questionnaire is currently being prepared. ' .
                    'Please check back later for updates.' .
                '</p>';

            return $this->row($icon, $description, null);
        }

        return null;
    }

    /**
     * "Survey is has been prepared" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function surveyReadyRow($params)
    {
        $icon = $this->glyphicon($params['surveyExists']);

        if ($params['onCurrentStep'] && ! $params['surveyActive'] && ! $params['surveyComplete']) {
            $params['description'] .=
                '<p class="alert alert-info">' .
                    'Your community\'s questionnaire is currently being prepared. ' .
                    'Please check back later for updates.' .
                '</p>';
        }

        return $this->row($icon, $params['description'], null);
    }

    /**
     * "Invite respondents" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function invitationRow($params)
    {
        $icon = $this->glyphicon($params['invitationsSent']);

        if ($params['surveyActive']) {
            $buttonClass = 'btn btn-';
            $buttonClass .= ($params['invitationsSent'] ? 'default' : 'primary');
            if ($this->getUserRole() == 'admin') {
                $path = [
                    'prefix' => 'admin',
                    'controller' => 'Surveys',
                    'action' => 'invite',
                    $params['surveyId']
                ];
            } else {
                $path = [
                    'prefix' => 'client',
                    'controller' => 'Surveys',
                    'action' => 'invite',
                    'officials'
                ];
            }
            $actions = $this->Html->link(
                'Send ' . ($params['invitationsSent'] ? 'More ' : '') . 'Invitations',
                $path,
                ['class' => $buttonClass]
            );
        } else {
            $actions = null;
        }

        return $this->row($icon, $params['description'], $actions);
    }

    /**
     * "Responses received / Import responses" row
     *
     * @param array $params Parameters
     * @return string
     * @throws InternalErrorException
     */
    public function responsesRow($params)
    {
        $icon = $this->glyphicon($params['responsesReceived']);
        if ($params['onCurrentStep'] && $params['surveyActive']) {
            $params['description'] .=
                '<button class="btn btn-link importing_note_toggler">' .
                    '<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>' .
                '</button>';
        }

        $description = '<p>' . $params['description'] . '</p>';

        if ($params['onCurrentStep'] && $params['surveyActive']) {
            $description .=
                '<p class="importing_note" style="display: none;">' .
                    'Responses are automatically imported from SurveyMonkey' .
                    ($params['autoImportFrequency'] ? ' approximately ' . $params['autoImportFrequency'] : '') .
                    ', but you can manually import them at any time.' .
                '</p>';
        }

        if ($params['timeResponsesLastChecked']) {
            $description .=
                '<div class="last_import alert alert-info">' .
                    'New responses were last checked for ' .
                    $this->Time->timeAgoInWords($params['timeResponsesLastChecked'], ['end' => '+1 year']) .
                '</div>';
        }

        if ($params['importErrors']) {
            $description .= '<div class="import-results alert alert-danger">';
            $description .= __n('An error was', 'Errors were', count($params['importErrors']));
            $description .= ' encountered the last time responses were imported:';
            $description .= '<ul>';
            foreach ($params['importErrors'] as $error) {
                $description .= "<li>$error</li>";
            }
            $description .= '</ul></div>';
        } else {
            $description .= '<div class="import-results"></div>';
        }

        $actions = '';
        if ($params['surveyActive']) {
            $actions .= '<button class="btn btn-default btn-block import_button" data-survey-id="';
            $actions .= $params['surveyId'] . '">Import Responses</button>';
            if ($params['responsesReceived']) {
                $actions .= '<br />';
            }
        }
        if ($params['responsesReceived']) {
            if ($params['step'] == 2) {
                $surveyType = 'official';
            } elseif ($params['step'] == 3) {
                $surveyType = 'organization';
            } else {
                throw new InternalErrorException('"' . $params['step'] . '" is not a valid step.');
            }
            if ($this->getUserRole() == 'admin') {
                $path = [
                    'prefix' => 'admin',
                    'controller' => 'Respondents',
                    'action' => 'view',
                    $params['surveyId']
                ];
            } else {
                $path = [
                    'prefix' => 'client',
                    'controller' => 'Respondents',
                    'action' => 'index',
                    $surveyType
                ];
            }

            $actions .= $this->Html->link(
                'Review Respondents',
                $path,
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
        $icon = $this->glyphicon($params['thresholdReached']);

        if ($params['surveyActive'] && $params['responsesReceived']) {
            if ($this->getUserRole() == 'admin') {
                $path = [
                    'prefix' => 'admin',
                    'controller' => 'Surveys',
                    'action' => 'remind',
                    $params['surveyId']
                ];
            } else {
                $path = [
                    'prefix' => 'client',
                    'controller' => 'Surveys',
                    'action' => 'remind',
                    'official'
                ];
            }
            $actions = $this->Html->link(
                'Reminders',
                $path,
                ['class' => 'btn btn-default']
            );
        } else {
            $actions = null;
        }

        return $this->row($icon, $params['description'], $actions);
    }

    /**
     * "Addressing unapproved responses" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function unapprovedResponsesRow($params)
    {
        $icon = $this->glyphicon($params['allUnapprovedAddressed']);

        if ($params['hasUninvitedResponses']) {
            if ($this->getUserRole() == 'admin') {
                $path = [
                    'prefix' => 'admin',
                    'controller' => 'Respondents',
                    'action' => 'unapproved',
                    $params['surveyId']
                ];
            } else {
                $path = [
                    'prefix' => 'client',
                    'controller' => 'Respondents',
                    'action' => 'unapproved',
                    'official'
                ];
            }
            $actions = $this->Html->link(
                'Approve / Dismiss',
                $path,
                ['class' => 'btn btn-' . ($params['allUnapprovedAddressed'] ? 'default' : 'primary')]
            );
        } else {
            $actions = null;
        }

        return $this->row($icon, $params['description'], $actions);
    }

    /**
     * "Purchased community organizations survey" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function orgSurveyPurchasedRow($params)
    {
        $icon = $this->glyphicon($params['purchased']);

        if ($params['purchased'] || $params['optedOut']) {
            $actions = null;
        } else {
            $actions =
                '<a href="' . $params['purchaseUrl'] . '" class="btn btn-primary">' .
                    'Purchase Now' .
                '</a>' .
                $this->optOutLink(ProductsTable::ORGANIZATIONS_SURVEY, $params['communityId']);
        }

        return $this->row($icon, $params['description'], $actions);
    }

    /**
     * "PWRRR policy development has been purchased" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function policyDevPurchasedRow($params)
    {
        $icon = $this->glyphicon($params['purchased']);

        if ($params['purchased']) {
            $actions = null;
        } else {
            $actions =
                '<a href="' . $params['purchaseUrl'] . '" class="btn btn-primary">' .
                    'Purchase Now' .
                '</a>';
        }

        return $this->row($icon, $params['description'], $actions);
    }

    /**
     * "Presentation A/B/C has been scheduled" row
     *
     * @param string $letter An uppercase letter (A, B, or C)
     * @param \Cake\I18n\Date|null $date Date
     * @return string
     */
    public function presentationScheduledRow($letter, $date)
    {
        $description = 'Scheduled Presentation ' . strtoupper($letter);
        if ($date == null) {
            $description .= '<br /> You will be contacted by a CRI representative ' .
                'to schedule Presentation ' . strtoupper($letter) . '.';
        } else {
            $description .= ' for ' . $this->Time->format($date, 'MMMM d, Y', false, 'America/New_York');
        }
        $icon = $this->glyphicon($date != null);

        return $this->row($icon, $description, null);
    }

    /**
     * "Presentation A/B/C has taken place" row
     *
     * @param string $letter An uppercase letter (A, B, or C)
     * @param \Cake\I18n\Date|null $date Date
     * @return string
     */
    public function presentationCompletedRow($letter, $date)
    {
        $description = 'Completed Presentation ' . strtoupper($letter);
        $completed = $date != null && $date->format('Y-m-d') <= date('Y-m-d');
        $icon = $this->glyphicon($completed);

        return $this->row($icon, $description, null);
    }

    /**
     * "Leadership Summit has been purchased" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function leadershipSummitRow(array $params)
    {
        $icon = $this->glyphicon($params['purchased']);

        if ($params['purchased'] || $params['optedOut']) {
            $actions = null;
        } else {
            $actions =
                '<a href="' . $params['purchaseUrl'] . '" class="btn btn-primary">' .
                    'Purchase Now' .
                '</a>' .
                $this->optOutLink(ProductsTable::OFFICIALS_SUMMIT, $params['communityId']);
        }

        return $this->row($icon, $params['description'], $actions);
    }

    /**
     * "Facilitated Community Awareness Conversation has been purchased" row
     *
     * @param array $params Parameters
     * @return string
     */
    public function orgsSummitRow(array $params)
    {
        $icon = $this->glyphicon($params['purchased']);

        if ($params['purchased']) {
            $actions = null;
        } else {
            $actions =
                '<a href="' . $params['purchaseUrl'] . '" class="btn btn-primary">' .
                'Purchase Now' .
                '</a>';
        }

        return $this->row($icon, $params['description'], $actions);
    }

    /**
     * Returns a string for a purchase opt-out link
     *
     * @param int $productId Product ID
     * @param null|int $communityId Community ID (if user is an admin)
     * @return string
     * @throws InternalErrorException
     */
    public function optOutLink($productId, $communityId = null)
    {
        if ($this->getUserRole() == 'admin') {
            if (! $communityId) {
                throw new InternalErrorException('Cannot create opt-out link. Community ID missing.');
            }
            $optOutUrl = Router::url([
                'prefix' => 'admin',
                'controller' => 'OptOuts',
                'action' => 'optOut',
                $communityId,
                $productId
            ]);
        } else {
            $optOutUrl = Router::url([
                'prefix' => 'client',
                'controller' => 'OptOuts',
                'action' => 'optOut',
                $productId
            ]);
        }
        return
            '<a href="' . $optOutUrl . '" class="btn btn-default opt-out">' .
                'Opt Out' .
            '</a>';
    }
}
