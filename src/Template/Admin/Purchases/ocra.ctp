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

<?php foreach ($purchases as $label => $purchaseGroup): ?>
    <section class="ocra-billing">
        <h1>
            <?= ucwords($label) ?>
        </h1>
        <?php if ($purchaseGroup): ?>
            <?php $hyphenatedLabel = str_replace(' ', '-', $label); ?>
            <button class="btn btn-default" data-toggle="collapse" data-target="#<?= $hyphenatedLabel ?>">
                <?= count($purchaseGroup) ?>
                <?= __n('charge', 'charges', count($purchaseGroup)) ?>
                ($<?= number_format($totals[$label]) ?>)
            </button>
            <div class="collapse" id="<?= $hyphenatedLabel ?>">
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
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchaseGroup as $purchase): ?>
                            <tr>
                                <td>
                                    <?= $this->Time->format($purchase->created, 'M/d/YYYY', false, 'America/New_York'); ?>
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
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>
                No charges
            </p>
        <?php endif; ?>
    </section>
<?php endforeach; ?>
