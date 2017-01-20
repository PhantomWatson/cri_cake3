<?= $this->ClientHome->tbodyForStep(2, $score) ?>
    <tr>
        <th colspan="3">
            <button class="step-header step-header-expandable">
                Step Two: Leadership Alignment Assessment
            </button>
        </th>
    </tr>

    <?= $this->ClientHome->surveyReadyRow([
        'description' => $criteria[2]['survey_created'][0],
        'onCurrentStep' => ($score == 2),
        'surveyActive' => $surveyIsActive['official'],
        'surveyComplete' => $surveyIsComplete['official'],
        'surveyExists' => $surveyExists['official']
    ]) ?>

    <?= $this->ClientHome->invitationRow([
        'surveyId' => $officialSurveyId,
        'description' => $criteria[2]['invitations_sent'][0],
        'invitationsSent' => $criteria[2]['invitations_sent'][1],
        'surveyActive' => $surveyIsActive['official']
    ]) ?>

    <?= $this->ClientHome->responsesRow([
        'autoImportFrequency' => $autoImportFrequency,
        'description' => $criteria[2]['responses_received'][0],
        'importErrors' => $importErrors['official'],
        'onCurrentStep' => ($score == 2),
        'responsesReceived' => $criteria[2]['responses_received'][1],
        'step' => 2,
        'surveyActive' => $surveyIsActive['official'],
        'surveyId' => $officialSurveyId,
        'timeResponsesLastChecked' => $officialResponsesChecked
    ]) ?>

    <?= $this->ClientHome->responseRateRow([
        'description' => $criteria[2]['response_threshhold_reached'][0],
        'responsesReceived' => $criteria[2]['responses_received'][1],
        'surveyActive' => $surveyIsActive['official'],
        'surveyId' => $officialSurveyId,
        'thresholdReached' => $criteria[2]['response_threshhold_reached'][1]
    ]) ?>

    <?= $this->ClientHome->unapprovedResponsesRow([
        'allUnapprovedAddressed' => $criteria[2]['unapproved_addressed'][1],
        'description' => $criteria[2]['unapproved_addressed'][0],
        'hasUninvitedResponses' => $criteria[2]['hasUninvitedResponses'],
        'surveyId' => $officialSurveyId
    ]) ?>

    <?= $this->ClientHome->presentationScheduledRow('A', $community->presentation_a) ?>

    <?= $this->ClientHome->presentationCompletedRow('A', $community->presentation_a) ?>

    <?= $this->ClientHome->presentationScheduledRow('B', $community->presentation_b) ?>

    <?= $this->ClientHome->presentationCompletedRow('B', $community->presentation_b) ?>

    <?= $this->ClientHome->orgSurveyPurchasedRow([
        'description' => $step2SurveyPurchased[0],
        'purchased' => $step2SurveyPurchased[1],
        'purchaseUrl' => $purchaseUrls[3]
    ]) ?>
</tbody>
