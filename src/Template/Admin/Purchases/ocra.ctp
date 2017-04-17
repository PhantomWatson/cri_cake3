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

<?php foreach ($purchases as $label => $group): ?>
    <section class="ocra-billing">
        <h1>
            <?= ucwords($label) ?>
        </h1>
        <?php if ($group['purchases']): ?>
            <?php $hyphenatedLabel = str_replace(' ', '-', $label); ?>

            <button class="btn btn-default" data-toggle="collapse" data-target="#<?= $hyphenatedLabel ?>">
                <?= count($group['purchases']) ?>
                <?= __n('charge', 'charges', count($group['purchases'])) ?>
                ($<?= number_format($totals[$label]) ?>)
            </button>

            <div class="collapse" id="<?= $hyphenatedLabel ?>">

                <?php if ($group['form']): ?>
                    <?= $this->Form->create(null, [
                        'url' => $group['form']['action']
                    ]) ?>
                <?php endif; ?>

                <table class="table">
                    <thead>
                        <tr>
                            <th>
                                Date
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
                                    <?= $this->Time->format(
                                        $purchase->created,
                                        'M/d/YYYY',
                                        false,
                                        'America/New_York'
                                    ); ?>
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

                    <?php if ($group['form']): ?>
                        <tfoot>
                            <tr class="select-all">
                                <td colspan="3"></td>
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
                        </tfoot>
                    <?php endif; ?>

                </table>

                <?php if ($group['form']): ?>
                    <?= $this->Form->end() ?>
                <?php endif; ?>

            </div>
        <?php else: ?>
            <p>
                No charges
            </p>
        <?php endif; ?>
    </section>
<?php endforeach; ?>

<?php $this->append('buffered'); ?>
    ocraPayments.init();
<?php $this->end(); ?>
