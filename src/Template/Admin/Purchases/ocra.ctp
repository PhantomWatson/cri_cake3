<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-arrow-left"></span> Back to Payment Records',
        [
            'prefix' => 'admin',
            'controller' => 'Purchases',
            'action' => 'index'
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
</p>

<p class="well">
    The following are the non-refunded charges whose funding source is OCRA. Charges become billable when their
    products (e.g. presentations) are delivered. Select charges and click 'Update' in order to mark charges
    as billed or paid.
</p>

<div>
    <ul class="nav nav-tabs ocra-billing" role="tablist">
        <?php foreach ($purchases as $label => $group): ?>
            <?php
                $hyphenatedLabel = str_replace(' ', '-', $label);
                $first = $label == 'not yet billable';
            ?>
            <li role="presentation" <?php if ($first): ?>class="active"<?php endif; ?>>
                <a href="#<?= $hyphenatedLabel ?>" aria-controls="<?= $hyphenatedLabel ?>" role="tab" data-toggle="tab">
                    <?= ucwords($label) ?>
                    <span class="badge">
                        <?= count($group['purchases']) ?>
                        <?php /* ($<?= number_format($totals[$label]) ?>) */ ?>
                    </span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content ocra-billing">
        <?php foreach ($purchases as $label => $group): ?>
            <?php
                $hyphenatedLabel = str_replace(' ', '-', $label);
                $first = $label == 'not yet billable';
            ?>
            <div role="tabpanel" class="tab-pane <?php if ($first): ?>active<?php endif; ?>" id="<?= $hyphenatedLabel ?>">
                <?php if ($group['purchases']): ?>
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
                                        ($<?= number_format($purchase->product['price']) ?>)

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
                <?php else: ?>
                    <p class="well no-charges">
                        No charges
                    </p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php $this->append('buffered'); ?>
    ocraPayments.init();
<?php $this->end(); ?>
