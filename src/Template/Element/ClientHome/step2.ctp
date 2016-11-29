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
            <?php if ($leadershipSurveyCreated && $surveyIsActive['official']): ?>
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
                <?php if ($score == 2 && $surveyIsActive['official']): ?>
                    <button class="btn btn-link importing_note_toggler">
                        <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                    </button>
                <?php endif; ?>
            </p>

            <?php if ($score == 2 && $surveyIsActive['official']): ?>
                <p class="importing_note" style="display: none;">
                    Responses are automatically imported from
                    SurveyMonkey<?= $autoImportFrequency ? ' approximately '.$autoImportFrequency : '' ?>,
                    but you can manually import them at any time.
                </p>
            <?php endif; ?>

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
            <?php if ($surveyIsActive['official']): ?>
                <button class="btn btn-default btn-block import_button" data-survey-id="<?= $officialSurveyId ?>">
                    Import Responses
                </button>
            <?php endif; ?>
            <?php if ($surveyIsActive['official'] && $criteria[2]['responses_received'][1]): ?>
                <br />
            <?php endif; ?>
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
            <?php if ($surveyIsActive['official']): ?>
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
            <?php endif; ?>
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