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
            <?php if ($organizationSurveyOpen && $surveyIsActive['organization']): ?>
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
                <?php if ($score == 3 && $surveyIsActive['organization']): ?>
                    <button class="btn btn-link importing_note_toggler">
                        <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                    </button>
                <?php endif; ?>
            </p>

            <?php if ($score == 3 && $surveyIsActive['organization']): ?>
                <p class="importing_note" style="display: none;">
                    Responses are automatically imported from
                    SurveyMonkey<?= $autoImportFrequency ? ' approximately '.$autoImportFrequency : '' ?>,
                    but you can manually import them at any time.
                </p>
            <?php endif; ?>

            <?php if ($organizationResponsesChecked): ?>
                <div class="last_import alert alert-info">
                    New responses were last checked for
                    <?= $this->Time->timeAgoInWords($organizationResponsesChecked, ['end' => '+1 year']) ?>
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
            <?php if ($surveyIsActive['organization']): ?>
                <button class="btn btn-default import_button" data-survey-id="<?= $organizationSurveyId ?>">
                    Import Responses
                </button>
            <?php endif; ?>
            <?php if ($surveyIsActive['organization'] && $criteria[3]['responses_received'][1]): ?>
                <br />
            <?php endif; ?>
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