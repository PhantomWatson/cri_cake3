<?php
    use Cake\Validation\Validation;
?>

<div class="survey_overview">
    <?php if (! $isOpen): ?>
        <p class="alert alert-info">
            Note: This questionnaire is not yet ready to be administered.
        </p>
    <?php endif; ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                Activate
            </h3>
        </div>
        <div class="panel-body">
            <p>
                <?php
                    $stepForSurvey = ($survey['type'] == 'official') ? 2 : 3;
                    $currentStep = $community['score'];
                ?>
                <?= $community['name'] ?>'s community <?= $survey['type'] ?>s questionnaire is currently
                <?php if ($currentlyActive): ?>
                    <span class="text-success">active</span>
                    <?php if ($currentStep < $stepForSurvey): ?>
                        even though the community has not yet advanced to Step <?= $stepForSurvey ?>.
                    <?php elseif ($currentStep == $stepForSurvey): ?>
                        and should remain active until this community is advanced to Step <?= $stepForSurvey + 1 ?>.
                    <?php else: ?>
                        and is <strong>ready to be deactivated</strong>.
                    <?php endif; ?>
                <?php else: ?>
                    <span class="text-danger">inactive</span>
                    <?php if ($currentStep < $stepForSurvey): ?>
                        and should be activated when the community advances to Step <?= $stepForSurvey ?>.
                    <?php elseif ($currentStep == $stepForSurvey): ?>
                        and is <strong>ready to be activated</strong>.
                    <?php else: ?>
                        because it has been finalized.
                    <?php endif; ?>
                <?php endif; ?>
            </p>

            <?= $this->Html->link(
                $currentlyActive ? 'Deactivate' : 'Activate',
                [
                    'prefix' => 'admin',
                    'controller' => 'Surveys',
                    'action' => 'activate',
                    $survey['id']
                ],
                ['class' => 'btn btn-default']
            ) ?>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                Invite
            </h3>
        </div>
        <div class="panel-body">
            <p>
                <?= $invitedRespondentCount ?>
                community
                <?= __n("{$survey['type']} has been sent a questionnaire invitation", "{$survey['type']}s have been sent questionnaire invitations", $invitedRespondentCount) ?>
            </p>
            <p>
                <?php if ($invitedRespondentCount > 0): ?>
                    <button class="btn btn-default invitations_toggler">
                        View Invitations
                    </button>
                <?php endif; ?>
                <?= $this->Html->link(
                    'Send Invitations',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Surveys',
                        'action' => 'invite',
                        $survey['id']
                    ],
                    ['class' => 'btn btn-default']
                ) ?>
                <?php if ($invitedRespondentCount > 0): ?>
                    <?= $this->Html->link(
                        'Reminders',
                        [
                            'prefix' => 'admin',
                            'controller' => 'Surveys',
                            'action' => 'remind',
                            $survey['id']
                        ],
                        ['class' => 'btn btn-default']
                    ) ?>
                <?php endif; ?>
            </p>
            <?php if ($invitedRespondentCount > 0): ?>
                <div class="invitations_list">
                    <p>
                        Invitations sent out for this questionnaire:
                    </p>
                    <ul>
                        <?php foreach ($invitations as $invitation): ?>
                            <li>
                                <?= $invitation->name ?: '(No name)' ?>
                                <span class="email">
                                    <?php if (Validation::email($invitation->email)): ?>
                                        <a href="mailto:<?= $invitation->email ?>">
                                            <?= $invitation->email ?>
                                        </a>
                                    <?php else: ?>
                                        <?= $invitation->email ?: '(No email)' ?>
                                    <?php endif; ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                Collect
            </h3>
        </div>
        <div class="panel-body">
            <p>
                <span class="last_import_time">
                    <?php if ($responsesChecked): ?>
                        Responses were last imported
                        <strong>
                            <?= $this->Time->timeAgoInWords($responsesChecked, ['end' => '+1 year']) ?>
                        </strong>
                    <?php else: ?>
                        Responses have not been imported yet
                    <?php endif; ?>
                </span>
            </p>
            <p>
                <?php if ($isAutomaticallyImported): ?>
                    Responses are automatically imported from
                    SurveyMonkey<?= $autoImportFrequency ? ' approximately '.$autoImportFrequency : '' ?>
                    while this community is in stage <?= $stageForAutoImport ?> of CRI,
                    but you can manually import them at any time.
                <?php else: ?>
                    New responses to this questionnaire are <strong>not</strong> being automatically imported from SurveyMonkey because
                    this community is not currently in stage <?= $stageForAutoImport ?> of CRI.
                <?php endif; ?>
            </p>
            <button class="btn btn-default import_button" data-survey-id="<?= $survey['id'] ?>">
                Import Responses
            </button>
            <?php if ($survey['import_errors']): ?>
                <?php $errors = unserialize($survey['import_errors']); ?>
                <div id="import-results" class="alert alert-danger">
                    <?= __n('An error was', 'Errors were', count($errors)) ?> encountered the last time responses were imported:
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li>
                                <?= $error ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <div id="import-results"></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                Review
            </h3>
        </div>
        <div class="panel-body">
            <p>
                <?php
                    if ($percentInvitedResponded < 33) {
                        echo '<span class="text-danger">';
                    } elseif ($percentInvitedResponded < 66) {
                        echo '<span class="text-warning">';
                    } else {
                        echo '<span class="text-success">';
                    }
                    echo $percentInvitedResponded.'%</span>';
                ?>
                of invited respondents have completed this questionnaire
            </p>

            <?php if ($hasUninvitedUnaddressed): ?>
                <p>
                    <span class="text-warning">
                        This questionnaire has uninvited responses that need to be approved or dismissed.
                    </span>
                    <br />
                    These responses will <strong>not</strong> be included in this community's alignment assessment unless if they are approved.
                </p>
            <?php endif; ?>

            <?php if (isset($hasNewResponses) && $hasNewResponses): ?>
                <p>
                    <strong>
                        New responses have been received
                    </strong>
                    since this community's alignment was last set by an administrator.
                </p>
            <?php endif; ?>

            <?php
                $buttonClass = (isset($hasNewResponses) && $hasNewResponses) ? 'primary' : 'default';
                echo $this->Html->link(
                    'Review and Update Alignment',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Responses',
                        'action' => 'view',
                        $survey['id']
                    ],
                    ['class' => 'btn btn-'.$buttonClass]
                );
            ?>

            <?php if ($survey['type'] == 'official' && $uninvitedRespondentCount > 0): ?>
                <?= $this->Html->link(
                    'Review / Approve Uninvited Responses',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Respondents',
                        'action' => 'unapproved',
                        $survey['id']
                    ],
                    ['class' => 'btn btn-default']
                ) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
