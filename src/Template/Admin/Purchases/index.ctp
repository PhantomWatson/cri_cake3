<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?= $this->Html->link(
        'Add Purchase Record',
        [
            'prefix' => 'admin',
            'controller' => 'Purchases',
            'action' => 'add'
        ],
        ['class' => 'btn btn-success']
    ) ?>
</p>

<?= $this->element('pagination') ?>

<table class="table" id="purchases_index">
    <thead>
        <tr>
            <th>
                Community
            </th>
            <th>
                Product
            </th>
            <th>
                Date
            </th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($purchases as $purchase): ?>
            <tr>
                <td>
                    <?= $purchase->community['name'] ?>
                </td>
                <td>
                    <?php if ($purchase->product['description']): ?>
                        <?= $purchase->product['description'] ?>
                    <?php else: ?>
                        <span class="unknown">
                            Unknown product
                        </span>
                    <?php endif; ?>

                    <?php if ($purchase->product['price']): ?>
                        ($<?= number_format($purchase->product['price']) ?>)
                    <?php endif; ?>

                    <?php if ($purchase->refunded): ?>
                        <p class="refunded">
                            Marked <strong>refunded</strong> by
                            <?php if ($purchase->refunder['name']): ?>
                                <?= $purchase->refunder['name'] ?>
                            <?php else: ?>
                                an unknown user
                            <?php endif; ?>
                            on
                            <?= $purchase->refunded->format('F j, Y') ?>
                        </p>
                    <?php endif; ?>
                </td>
                <td>
                    <?= $purchase->created->format('F j, Y') ?>
                </td>
                <td>
                    <?php if (! $purchase->refunded): ?>
                        <?= $this->Form->postLink(
                            'Report Refund',
                            [
                                'prefix' => 'admin',
                                'action' => 'refund',
                                $purchase->id
                            ],
                            [
                                'class' => 'btn btn-default',
                                'escape' => false,
                                'confirm' => 'Are you sure you want to mark this payment as having been refunded?'
                            ]
                        ) ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->element('pagination') ?>