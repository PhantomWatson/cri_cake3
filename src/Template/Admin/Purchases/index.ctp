<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?= $this->element('pagination') ?>

<table class="table" id="purchases_index">
    <thead>
        <tr>
            <th>
                User
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
                    <?= $purchase->user['name'] ?>
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
                        ($<?= number_format($purchase->product['price'] / 100) ?>)
                    <?php endif; ?>

                    <?php if ($purchase->purchase['refunded']): ?>
                        <p class="refunded">
                            Marked <strong>refunded</strong> by
                            <?php if ($purchase->refunder['name']): ?>
                                <?= $purchase->refunder['name'] ?>
                            <?php else: ?>
                                an unknown user
                            <?php endif; ?>
                            on
                            <?php
                                $timestamp = strtotime($purchase->purchase['refunded']);
                                echo date('F j, Y', $timestamp);
                            ?>
                        </p>
                    <?php endif; ?>
                </td>
                <td>
                    <?= $purchase->created->format('F j, Y') ?>
                </td>
                <td>
                    <?php if (! $purchase->purchase['refunded']): ?>
                        <?= $this->Form->postLink(
                            'Report Refund',
                            [
                                'prefix' => 'admin',
                                'action' => 'refund',
                                $purchase->purchase['id']
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