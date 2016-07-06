<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php
    function glyphicon($bool) {
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
    function tbodyForStep($tbodyStep, $currentStep) {
        if ($tbodyStep > floor($currentStep)) {
            return '<tbody class="future">';
        }
        if ($tbodyStep < floor($currentStep)) {
            return '<tbody class="past">';
        }
        return '<tbody class="current">';
    }
?>

<?php if ($authUser['role'] == 'admin'): ?>
    <p>
        <?= $this->Html->link(
            'Change community / client',
            [
                'prefix' => 'admin',
                'controller' => 'Users',
                'action' => 'chooseClient'
            ],
            [
                'class' => 'btn btn-default'
            ]
        ) ?>
    </p>
<?php endif; ?>

<div id="client_home">
    <table>
        <?= tbodyForStep(1, $score) ?>
            <tr>
                <th colspan="3">
                    <button class="step-header">
                        Step One: Sign Up
                    </button>
                </th>
            </tr>
            <tr>
                <td>
                    <?= glyphicon($criteria[1]['survey_purchased'][1]) ?>
                </td>
                <td>
                    <?= $criteria[1]['survey_purchased'][0] ?>
                </td>
                <td>
                    <?php if (! $criteria[1]['survey_purchased'][1]): ?>
                        <a href="<?= $purchaseUrls[1] ?>" class="btn btn-primary">
                            Purchase Now
                        </a>
                    <?php endif; ?>
                </td>
            </tr>

            <?php if (isset($criteria[1]['survey_created'])): ?>
                <?php $leadershipSurveyCreated = $criteria[1]['survey_created'][1]; ?>
                <tr>
                    <td>
                        <?= glyphicon($criteria[1]['survey_created'][1]) ?>
                    </td>
                    <td>
                        <?= $criteria[1]['survey_created'][0] ?>
                        <?php if (! $criteria[1]['survey_created'][1] && $score < 2): ?>
                            <p class="alert alert-info">
                                Your community's questionnaire is currently being prepared. Please check back later for updates.
                            </p>
                        <?php endif; ?>
                    </td>
                    <td>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>

        <?= tbodyForStep(2, $score) ?>
            <tr>
                <th colspan="3">
                    <button class="step-header">
                        Step Two: Leadership Alignment Assessment
                    </button>
                </th>
            </tr>

            <?php if (isset($criteria[2]['survey_created'])): ?>
                <?php $leadershipSurveyCreated = $criteria[2]['survey_created'][1] ?>
                <tr>
                    <td>
                        <?= glyphicon($criteria[2]['survey_created'][1]) ?>
                    </td>
                    <td>
                        <?= $criteria[2]['survey_created'][0] ?>
                        <?php if (! $criteria[2]['survey_created'][1] && $score >= 2 && $score < 3): ?>
                            <p class="alert alert-info">
                                Your community's questionnaire is currently being prepared. Please check back later for updates.
                            </p>
                        <?php endif; ?>
                    </td>
                    <td>
                    </td>
                </tr>
            <?php endif; ?>

            <tr>
                <td>
                    <?= glyphicon($criteria[2]['invitations_sent'][1]) ?>
                </td>
                <td>
                    <?= $criteria[2]['invitations_sent'][0] ?>
                </td>
                <td>
                    <?php if ($leadershipSurveyCreated): ?>
                        <?= $this->Html->link(
                            'Send '.($criteria[2]['invitations_sent'][1] ? 'More ' : '').'Invitations',
                            [
                                'prefix' => 'client',
                                'controller' => 'Surveys',
                                'action' => 'invite',
                                'officials'
                            ],
                            ['class' => 'btn btn-'.($criteria[2]['invitations_sent'][1] ? 'default' : 'primary')]
                        ) ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?= glyphicon($criteria[2]['responses_received'][1]) ?>
                </td>
                <td>
                    <p>
                        <?= $criteria[2]['responses_received'][0] ?>
                        <button class="btn btn-link importing_note_toggler">
                            <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                        </button>
                    </p>

                    <p class="importing_note" style="display: none;">
                        <?php if ($score < 3): ?>
                            Responses are automatically imported from
                            SurveyMonkey<?= $autoImportFrequency ? ' approximately '.$autoImportFrequency : '' ?>,
                            but you can manually import them at any time.
                        <?php else: ?>
                            New responses to this questionnaire are no longer being automatically imported from SurveyMonkey.
                        <?php endif; ?>
                    </p>

                    <?php if ($officialResponsesChecked): ?>
                        <div class="last_import alert alert-info">
                            New responses were last checked for
                            <?= $this->Time->timeAgoInWords($officialResponsesChecked, ['end' => '+1 year']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($importErrors['official']): ?>
                        <div class="import-results alert alert-danger">
                            <?= __n('An error was', 'Errors were', count($importErrors['official'])) ?> encountered the last time responses were imported:
                            <ul>
                                <?php foreach ($importErrors['official'] as $error): ?>
                                    <li>
                                        <?= $error ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="import-results"></div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($leadershipSurveyCreated): ?>
                        <button class="btn btn-default btn-block import_button" data-survey-id="<?= $officialSurveyId ?>">
                            Import Responses
                        </button>
                        <br />
                        <?php if ($criteria[2]['responses_received'][1]): ?>
                            <?= $this->Html->link(
                                'Review Responses',
                                [
                                    'prefix' => 'client',
                                    'controller' => 'Respondents',
                                    'action' => 'index',
                                    'official'
                                ],
                                ['class' => 'btn btn-default']
                            ) ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?= glyphicon($criteria[2]['response_threshhold_reached'][1]) ?>
                </td>
                <td>
                    <?= $criteria[2]['response_threshhold_reached'][0] ?>
                </td>
                <td>
                    <?= $this->Html->link(
                        'Reminders',
                        [
                            'prefix' => 'client',
                            'controller' => 'Surveys',
                            'action' => 'remind',
                            'official'
                        ],
                        ['class' => 'btn btn-default']
                    ) ?>
                </td>
            </tr>

            <?php if (isset($criteria[2]['unapproved_addressed'])): ?>
                <tr>
                    <td>
                        <?= glyphicon($criteria[2]['unapproved_addressed'][1]) ?>
                    </td>
                    <td>
                        <?= $criteria[2]['unapproved_addressed'][0] ?>
                    </td>
                    <td>
                        <?= $this->Html->link(
                            'Approve / Dismiss',
                            [
                                'prefix' => 'client',
                                'controller' => 'Respondents',
                                'action' => 'unapproved',
                                'official'
                            ],
                            ['class' => 'btn btn-'.($criteria[2]['unapproved_addressed'][1] ? 'default' : 'primary')]
                        ) ?>
                    </td>
                </tr>
            <?php endif; ?>

            <tr>
                <td>
                    <?= glyphicon($criteria[2]['alignment_calculated'][1]) ?>
                </td>
                <td>
                    <?= $criteria[2]['alignment_calculated'][0] ?>
                </td>
                <td>
                </td>
            </tr>

            <?php if (isset($criteria[2]['summit_purchased'])): ?>
                <tr>
                    <td>
                        <?= glyphicon($criteria[2]['summit_purchased'][1]) ?>
                    </td>
                    <td>
                        <?= $criteria[2]['summit_purchased'][0] ?>
                    </td>
                    <td>
                        <?php if (! $criteria[2]['summit_purchased'][1]): ?>
                            <a href="<?= $purchaseUrls[2]; ?>" class="btn btn-primary">
                                Purchase Now
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>

                <?php if ($step2Alignment): ?>
                    <tr>
                        <td>
                            <?= glyphicon($step2Alignment[1]) ?>
                        </td>
                        <td>
                            <?= $step2Alignment[0] ?>
                        </td>
                        <td>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php else: ?>
                <?php if ($step2Alignment): ?>
                    <tr>
                        <td>
                            <?= glyphicon($step2Alignment[1]) ?>
                        </td>
                        <td>
                            <?= $step2Alignment[0] ?>
                        </td>
                        <td>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($step2SurveyPurchased): ?>
                <tr>
                    <td>
                        <?= glyphicon($step2SurveyPurchased[1]) ?>
                    </td>
                    <td>
                        <?= $step2SurveyPurchased[0] ?>
                    </td>
                    <td>
                        <?php if (! $step2SurveyPurchased[1]): ?>
                            <a href="<?= $purchaseUrls[3]; ?>" class="btn btn-primary">
                                Purchase Now
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>

        <?php if ($fastTrack): ?>
            <?= tbodyForStep(3, $score) ?>
                <tr>
                    <th colspan="3">
                        <button class="step-header">
                            Step Three and Four skipped in Fast Track
                        </button>
                    </th>
                </tr>
            </tbody>
        <?php else: ?>
            <?= tbodyForStep(3, $score) ?>
                <tr>
                    <th colspan="3">
                        <button class="step-header">
                            Step Three: Community Organizations Alignment Assessment
                        </button>
                    </th>
                </tr>
                <tr>
                    <td>
                        <?= glyphicon($criteria[3]['survey_created'][1]) ?>
                    </td>
                    <td>
                        <?= $criteria[3]['survey_created'][0] ?>
                        <?php if (! $criteria[3]['survey_created'][1] && $score >= 3 && $score < 4): ?>
                            <p class="alert alert-info">
                                Your community's questionnaire is currently being prepared. Please check back later for updates.
                            </p>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($organizationSurveyOpen): ?>
                            <?= $this->Html->link(
                                'Send Invitations',
                                [
                                    'prefix' => 'client',
                                    'controller' => 'Surveys',
                                    'action' => 'invite',
                                    'organizations'
                                ],
                                ['class' => 'btn btn-default']
                            ) ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?= glyphicon($criteria[3]['responses_received'][1]) ?>
                    </td>
                    <td>
                        <p>
                            <?= $criteria[3]['responses_received'][0] ?>
                            <button class="btn btn-link importing_note_toggler">
                                <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                            </button>
                        </p>

                        <p class="importing_note" style="display: none;">
                            <?php if ($score < 4): ?>
                                Responses are automatically imported from
                                SurveyMonkey<?= $autoImportFrequency ? ' approximately '.$autoImportFrequency : '' ?>,
                                but you can manually import them at any time.
                            <?php else: ?>
                                New responses to this questionnaire are no longer being automatically imported from SurveyMonkey.
                            <?php endif; ?>
                        </p>

                        <?php if ($organizationResponsesChecked): ?>
                            <div class="last_import alert alert-info">
                                New responses were last checked for
                                <?= $this->Time->timeAgoInWords($organization, ['end' => '+1 year']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($importErrors['organization']): ?>
                            <div class="import-results alert alert-danger">
                                <?= __n('An error was', 'Errors were', count($importErrors['organization'])) ?> encountered the last time responses were imported:
                                <ul>
                                    <?php foreach ($importErrors['organization'] as $error): ?>
                                        <li>
                                            <?= $error ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="import-results"></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($criteria[3]['survey_created'][1]): ?>
                            <button class="btn btn-default import_button" data-survey-id="<?= $organizationSurveyId ?>">
                                Import Responses
                            </button>
                            <br />
                            <?php if ($criteria[3]['responses_received'][1]): ?>
                                <?= $this->Html->link(
                                    'Review Responses',
                                    [
                                        'prefix' => 'client',
                                        'controller' => 'Respondents',
                                        'action' => 'index',
                                        'organization'
                                    ],
                                    ['class' => 'btn btn-default']
                                ) ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?= glyphicon($criteria[3]['alignment_calculated'][1]) ?>
                    </td>
                    <td>
                        <?= $criteria[3]['alignment_calculated'][0] ?>
                    </td>
                    <td>
                    </td>
                </tr>

                <?php if (isset($criteria[3]['summit_purchased'])): ?>
                    <tr>
                        <td>
                            <?= glyphicon($criteria[3]['summit_purchased'][1]) ?>
                        </td>
                        <td>
                            <?= $criteria[3]['summit_purchased'][0] ?>
                        </td>
                        <td>
                            <?php if (! $criteria[3]['summit_purchased'][1]): ?>
                                <a href="<?= $purchaseUrls[4] ?>" class="btn btn-primary">
                                    Purchase Now
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php if ($step3Alignment): ?>
                        <tr>
                            <td>
                                <?= glyphicon($step3Alignment[1]) ?>
                            </td>
                            <td>
                                <?= $step3Alignment[0] ?>
                            </td>
                            <td>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($step3Alignment): ?>
                        <tr>
                            <td>
                                <?= glyphicon($step3Alignment[1]) ?>
                            </td>
                            <td>
                                <?= $step3Alignment[0] ?>
                            </td>
                            <td>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($step3PolicyDevPurchased): ?>
                    <tr>
                        <td>
                            <?= glyphicon($step3PolicyDevPurchased[1]) ?>
                        </td>
                        <td>
                            <?= $step3PolicyDevPurchased[0] ?>
                        </td>
                        <td>
                            <?php if (! $step3PolicyDevPurchased[1]): ?>
                                <a href="<?= $purchaseUrls[5] ?>" class="btn btn-primary">
                                    Purchase Now
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>

            <?= tbodyForStep(4, $score) ?>
                <tr>
                    <th colspan="3">
                        <button class="step-header">
                            Step Four: Review of Findings
                        </button>
                    </th>
                </tr>
                <tr>
                    <td>
                        <?= glyphicon($criteria[4]['meeting_scheduled'][1]) ?>
                    </td>
                    <td>
                        <?= $criteria[4]['meeting_scheduled'][0] ?>
                    </td>
                    <td>
                    </td>
                </tr>

                <?php if (isset($criteria[4]['meeting_held'])): ?>
                    <tr>
                        <td>
                            <?= glyphicon($criteria[4]['meeting_held'][1]) ?>
                        </td>
                        <td>
                            <?= $criteria[4]['meeting_held'][0] ?>
                        </td>
                        <td>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        <?php endif; ?>

        <?= tbodyForStep(5, $score) ?>
            <tr>
                <th colspan="3">
                    <button class="step-header">
                        Step Five: Conclusion
                    </button>
                </th>
            </tr>
        </tbody>
    </table>
</div>

<?php $this->element('script', ['script' => 'client']); ?>
<?php $this->append('buffered'); ?>
    clientHome.init();
<?php $this->end();
