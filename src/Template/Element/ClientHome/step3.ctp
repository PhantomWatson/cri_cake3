<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Community $community
 */
?>
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
        'surveyId' => $organizationSurveyId,
        'description' => $criteria[3]['invitations_sent'][0],
        'invitationsSent' => $criteria[3]['invitations_sent'][1],
        'surveyActive' => $surveyIsActive['organization'],
        'respondentType' => 'organizations'
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
        'description' => $criteria[3]['response_threshold_reached'][0],
        'responsesReceived' => $criteria[3]['responses_received'][1],
        'step' => 3,
        'surveyActive' => $surveyIsActive['organization'],
        'surveyId' => $organizationSurveyId,
        'thresholdReached' => $criteria[3]['response_threshold_reached'][1]
    ]) ?>

    <?= $this->ClientHome->presentationScheduledRow('C', $community->presentation_c) ?>

    <?= $this->ClientHome->presentationCompletedRow('C', $community->presentation_c) ?>

    <?php $optedOut = in_array(\App\Model\Table\ProductsTable::ORGANIZATIONS_SUMMIT, $optOuts); ?>
    <?= $this->ClientHome->orgsSummitRow([
        'communityId' => $community['id'],
        'description' => $criteria[3]['orgs_summit_purchased'][0],
        'optedOut' => $optedOut,
        'purchased' => $criteria[3]['orgs_summit_purchased'][1],
        'purchaseUrl' => $purchaseUrls[4]
    ]) ?>

    <?php if (! $optedOut && $criteria[3]['orgs_summit_purchased'][1]): ?>
        <?= $this->ClientHome->presentationScheduledRow('D', $community->presentation_d) ?>
        <?= $this->ClientHome->presentationCompletedRow('D', $community->presentation_d) ?>
    <?php endif; ?>

    <?= $this->ClientHome->policyDevPurchasedRow([
        'communityId' => $community['id'],
        'description' => $step3PolicyDevPurchased[0],
        'optedOut' => in_array(\App\Model\Table\ProductsTable::POLICY_DEVELOPMENT, $optOuts),
        'purchased' => $step3PolicyDevPurchased[1],
        'purchaseUrl' => $purchaseUrls[5]
    ]) ?>
</tbody>
