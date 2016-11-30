<?= $this->ClientHome->tbodyForStep(2, $score) ?>
    <tr>
        <th colspan="3">
            <button class="step-header">
                Step Two: Leadership Alignment Assessment
            </button>
        </th>
    </tr>

    <?= $this->ClientHome->surveyReadyRow([
        'surveyExists' => $surveyExists['official'],
        'surveyActive' => $surveyIsActive['official'],
        'surveyComplete' => $surveyIsComplete['official'],
        'description' => $criteria[2]['survey_created'][0],
        'onCurrentStep' => ($score == 2)
    ]) ?>

    <?= $this->ClientHome->invitationRow([
        'invitationsSent' => $criteria[2]['invitations_sent'][1],
        'surveyActive' => $surveyIsActive['official'],
        'description' => $criteria[2]['invitations_sent'][0]
    ]) ?>

    <?= $this->ClientHome->responsesRow([
        'autoImportFrequency' => $autoImportFrequency,
        'description' => $criteria[2]['responses_received'][0],
        'importErrors' => $importErrors['official'],
        'onCurrentStep' => ($score == 2),
        'responsesReceived' => $criteria[2]['responses_received'][1],
        'surveyActive' => $surveyIsActive['official'],
        'surveyId' => $officialSurveyId,
        'timeResponsesLastChecked' => $officialResponsesChecked,
    ]) ?>

    <?= $this->ClientHome->responseRateRow([
        'description' => $criteria[2]['response_threshhold_reached'][0],
        'responsesReceived' => $criteria[2]['responses_received'][1],
        'surveyActive' => $surveyIsActive['official'],
        'thresholdReached' => $criteria[2]['response_threshhold_reached'][1]
    ]) ?>

    <?= $this->ClientHome->unapprovedResponsesRow([
        'allUnapprovedAddressed' => $criteria[2]['unapproved_addressed'][1],
        'description' => $criteria[2]['unapproved_addressed'][0],
        'hasUninvitedResponses' => $criteria[2]['hasUninvitedResponses']
    ]) ?>

    <tr>
        <td>
            <?= $this->ClientHome->glyphicon($criteria[2]['alignment_calculated'][1]) ?>
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
                <?= $this->ClientHome->glyphicon($criteria[2]['summit_purchased'][1]) ?>
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
                    <?= $this->ClientHome->glyphicon($step2Alignment[1]) ?>
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
                    <?= $this->ClientHome->glyphicon($step2Alignment[1]) ?>
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
                <?= $this->ClientHome->glyphicon($step2SurveyPurchased[1]) ?>
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