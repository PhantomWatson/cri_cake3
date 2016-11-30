<?= $this->ClientHome->tbodyForStep(3, $score) ?>
    <tr>
        <th colspan="3">
            <button class="step-header">
                Step Three: Community Organizations Alignment Assessment
            </button>
        </th>
    </tr>

    <?= $this->ClientHome->surveyReadyRow([
        'surveyExists' => $surveyExists['organization'],
        'surveyActive' => $surveyIsActive['organization'],
        'surveyComplete' => $surveyIsComplete['organization'],
        'description' => $criteria[3]['survey_created'][0],
        'onCurrentStep' => ($score == 3)
    ]) ?>

    <?= $this->ClientHome->invitationRow([
        'invitationsSent' => $criteria[3]['invitations_sent'][1],
        'surveyActive' => $surveyIsActive['organization'],
        'description' => $criteria[3]['invitations_sent'][0]
    ]) ?>

    <?= $this->ClientHome->responsesRow([
        'autoImportFrequency' => $autoImportFrequency,
        'description' => $criteria[3]['responses_received'][0],
        'importErrors' => $importErrors['organization'],
        'onCurrentStep' => ($score == 3),
        'responsesReceived' => $criteria[3]['responses_received'][1],
        'surveyActive' => $surveyIsActive['organization'],
        'surveyId' => $organizationSurveyId,
        'timeResponsesLastChecked' => $organizationResponsesChecked,
    ]) ?>

    <?= $this->ClientHome->responseRateRow([
        'description' => $criteria[3]['response_threshhold_reached'][0],
        'responsesReceived' => $criteria[3]['responses_received'][1],
        'surveyActive' => $surveyIsActive['organization'],
        'thresholdReached' => $criteria[3]['response_threshhold_reached'][1]
    ]) ?>

    <tr>
        <td>
            <?= $this->ClientHome->glyphicon($criteria[3]['alignment_calculated'][1]) ?>
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
                <?= $this->ClientHome->glyphicon($criteria[3]['summit_purchased'][1]) ?>
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
                    <?= $this->ClientHome->glyphicon($step3Alignment[1]) ?>
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
                    <?= $this->ClientHome->glyphicon($step3Alignment[1]) ?>
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
                <?= $this->ClientHome->glyphicon($step3PolicyDevPurchased[1]) ?>
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