<?= $this->ClientHome->tbodyForStep(1, $score) ?>
    <tr>
        <th colspan="3">
            <button class="step-header step-header-expandable">
                Step One: Sign Up
            </button>
        </th>
    </tr>

    <?= $this->ClientHome->officialsSurveyPurchasedRow([
        'description' => $criteria[1]['survey_purchased'][0],
        'purchased' => $criteria[1]['survey_purchased'][1],
        'purchaseUrl' => $purchaseUrls[1]
    ]) ?>

    <?= $this->ClientHome->stepTwoPendingRow([
        'surveyPurchased' => $criteria[1]['survey_purchased'][1],
        'onStepOne' => ($score == 1)
    ]) ?>
</tbody>
