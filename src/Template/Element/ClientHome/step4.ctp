<?= $this->ClientHome->tbodyForStep(4, $score) ?>
    <tr>
        <th colspan="3">
            <button class="step-header">
                Step Four: Policy Development
            </button>
        </th>
    </tr>

    <?php if ($step3PolicyDevPurchased[1]): ?>
        <?= $this->ClientHome->policyDevDeliveredRow([
            'communityId' => $community['id'],
            'msg' => $step4PolicyDev[0],
            'delivered' => $step4PolicyDev[1]
        ]) ?>
    <?php endif; ?>
</tbody>