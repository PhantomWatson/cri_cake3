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

    <?= $this->ClientHome->alignmentCalculatedRow([
        'description' => $criteria[2]['alignment_calculated'][0],
        'alignmentCalculated' => $criteria[2]['alignment_calculated'][1]
    ]) ?>

    <?= $this->ClientHome->alignmentResultRow([
        'alignmentPassed' => $step2Alignment[1],
        'description' => $step2Alignment[0]
    ]) ?>

    <?= $this->ClientHome->orgSurveyPurchasedRow([
        'description' => $step2SurveyPurchased[0],
        'purchased' => $step2SurveyPurchased[1],
        'purchaseUrl' => $purchaseUrls[3]
    ]) ?>
</tbody>
