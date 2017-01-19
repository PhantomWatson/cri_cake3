<?= $this->ClientHome->tbodyForStep(3, $score) ?>
    <tr>
        <th colspan="3">
            <button class="step-header step-header-expandable">
                Step Three: Community Organizations Alignment Assessment
            </button>
        </th>
    </tr>

    <?= $this->ClientHome->surveyReadyRow([
        'description' => $criteria[3]['survey_created'][0],
        'onCurrentStep' => ($score == 3),
        'surveyActive' => $surveyIsActive['organization'],
        'surveyComplete' => $surveyIsComplete['organization'],
        'surveyExists' => $surveyExists['organization']
    ]) ?>

    <?= $this->ClientHome->invitationRow([
        'description' => $criteria[3]['invitations_sent'][0],
        'invitationsSent' => $criteria[3]['invitations_sent'][1],
        'surveyActive' => $surveyIsActive['organization']
    ]) ?>

    <?= $this->ClientHome->responsesRow([
        'autoImportFrequency' => $autoImportFrequency,
        'description' => $criteria[3]['responses_received'][0],
        'importErrors' => $importErrors['organization'],
        'onCurrentStep' => ($score == 3),
        'responsesReceived' => $criteria[3]['responses_received'][1],
        'step' => 3,
        'surveyActive' => $surveyIsActive['organization'],
        'surveyId' => $organizationSurveyId,
        'timeResponsesLastChecked' => $organizationResponsesChecked
    ]) ?>

    <?= $this->ClientHome->responseRateRow([
        'description' => $criteria[3]['response_threshhold_reached'][0],
        'responsesReceived' => $criteria[3]['responses_received'][1],
        'surveyActive' => $surveyIsActive['organization'],
        'thresholdReached' => $criteria[3]['response_threshhold_reached'][1]
    ]) ?>

    <?= $this->ClientHome->presentationScheduledRow('C', $community->presentation_c) ?>

    <?= $this->ClientHome->presentationCompletedRow('C', $community->presentation_c) ?>

    <?= $this->ClientHome->policyDevPurchasedRow([
        'description' => $step3PolicyDevPurchased[0],
        'purchased' => $step3PolicyDevPurchased[1],
        'purchaseUrl' => $purchaseUrls[5]
    ]) ?>
</tbody>
