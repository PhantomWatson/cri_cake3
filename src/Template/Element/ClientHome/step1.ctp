<?= $this->ClientHome->tbodyForStep(1, $score) ?>
    <tr>
        <th colspan="3">
            <button class="step-header">
                Step One: Sign Up
            </button>
        </th>
    </tr>

    <?= $this->ClientHome->officialsSurveyPurchasedRow([
        'description' => $criteria[1]['survey_purchased'][0],
        'purchased' => $criteria[1]['survey_purchased'][1],
        'purchaseUrl' => $purchaseUrls[1]
    ]) ?>

    <?php if (isset($criteria[1]['survey_created'])): ?>
        <?php $leadershipSurveyCreated = $criteria[1]['survey_created'][1]; ?>
        <tr>
            <td>
                <?= $this->ClientHome->glyphicon($criteria[1]['survey_created'][1]) ?>
            </td>
            <td>
                <?= $criteria[1]['survey_created'][0] ?>
                <?php if (! $criteria[1]['survey_created'][1] && $score < 2): ?>
                    <p class="alert alert-info">
                        Your community's questionnaire is currently being prepared. Please check back later for updates.
                    </p>
                <?php endif; ?>
            </td>
            <td>
            </td>
        </tr>
    <?php endif; ?>
</tbody>