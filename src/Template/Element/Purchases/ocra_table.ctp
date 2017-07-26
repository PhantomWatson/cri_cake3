<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Purchase $purchase
 */
?>
<?php if ($group['form']): ?>
    <?= $this->Form->create(null, [
        'url' => $group['form']['action']
    ]) ?>
<?php endif; ?>

<table class="table">
    <thead>
    <tr>
        <th>
            <?= $group['date'] ?>
        </th>
        <th>
            Community
        </th>
        <th>
            Product
        </th>
        <?php if ($group['form']): ?>
            <th class="action">
                <?= $group['form']['label'] ?>
            </th>
        <?php endif; ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($group['purchases'] as $i => $purchase): ?>
        <tr>
            <td>
                <?php
                $date = $group['date'] == 'Billed' ? $purchase->invoice->created : $purchase->created;
                echo $this->Time->format(
                    $date,
                    'M/d/YYYY',
                    false,
                    'America/New_York'
                );
                ?>
            </td>
            <td>
                <?= $purchase->community->name ?>
            </td>
            <td>
                <?= $purchase->product->description ?>
                ($<?= number_format($purchase->amount) ?>)
                <?php if ($purchase->amount != $purchase->product['price']): ?>
                    <br />
                    <span class="label label-warning">
                        Amount paid differs from current price
                    </span>
                <?php endif; ?>

                <?php if ($purchase->redunded): ?>
                    <span class="label label-warning">
                        Refunded
                    </span>
                <?php endif; ?>
            </td>
            <?php if ($group['form']): ?>
                <td class="action">
                    <?php
                    if ($label == 'billable') {
                        echo $this->Form->checkbox("purchaseIds[]", [
                            'value' => $purchase->id,
                            'hiddenField' => false
                        ]);
                    } elseif ($label == 'billed') {
                        echo $this->Form->checkbox("invoiceIds[]", [
                            'value' => $purchase->invoice->id,
                            'hiddenField' => false
                        ]);
                    }
                    ?>
                </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <?php if ($group['form']): ?>
        <tr class="select-all">
            <td colspan="3" class="total">
                <strong>
                    Total charges:
                </strong>
                $<?= number_format($totals[$label]) ?>
            </td>
            <td class="action">
                <button class="btn btn-link btn-sm" data-mode="1">
                    Select all
                </button>
            </td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td class="action">
                <?= $this->Form->button('Update', [
                    'class' => 'btn btn-default'
                ]) ?>
            </td>
        </tr>
    <?php else: ?>
        <tr class="select-all">
            <td colspan="3" class="total">
                <strong>
                    Total charges:
                </strong>
                $<?= number_format($totals[$label]) ?>
            </td>
        </tr>
    <?php endif; ?>
    </tfoot>
</table>

<?php if ($group['form']): ?>
    <?= $this->Form->end() ?>
<?php endif; ?>
