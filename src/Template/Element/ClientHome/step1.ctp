<?= $this->ClientHome->tbodyForStep(1, $score) ?>
    <tr>
        <th colspan="3">
            <button class="step-header step-header-expandable">
                Step One: Sign Up
            </button>
        </th>
    </tr>

    <?php $optedOut = in_array(\App\Model\Table\ProductsTable::OFFICIALS_SURVEY, $optOuts); ?>

    <?= $this->ClientHome->officialsSurveyPurchasedRow([
        'communityId' => $community['id'],
        'description' => $criteria[1]['survey_purchased'][0],
        'optedOut' => $optedOut,
        'purchased' => $criteria[1]['survey_purchased'][1],
        'purchaseUrl' => $purchaseUrls[1]
    ]) ?>

    <?php if (! $optedOut): ?>
        <?= $this->ClientHome->stepTwoPendingRow([
            'surveyPurchased' => $criteria[1]['survey_purchased'][1],
            'onStepOne' => ($score == 1)
        ]) ?>
    <?php endif; ?>
</tbody>
