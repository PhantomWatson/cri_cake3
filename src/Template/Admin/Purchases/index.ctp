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
            <th>
                Refund
            </th>
            <th>
                Details
            </th>
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
                </td>
                <td>
                    <?= $purchase->created->format('n/j/Y') ?>
                </td>
                <td>
                    <?php if ($purchase->refunded): ?>
                        <a class="refunded btn btn-default btn-block" href="#">
                            Refunded
                        </a>
                    <?php else: ?>
                        <?= $this->Form->postLink(
                            'Refund',
                            [
                                'prefix' => 'admin',
                                'action' => 'refund',
                                $purchase->id
                            ],
                            [
                                'class' => 'btn btn-default btn-block',
                                'escape' => false,
                                'confirm' => 'Are you sure you want to mark this payment as having been refunded?'
                            ]
                        ) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="#" class="details btn btn-default btn-block">
                        Details
                    </a>
                </td>
            </tr>
            <tr class="details">
                <td colspan="5">
                    <ul>
                        <li>
                            <?php if ($purchase->admin_added): ?>
                                Purchase record added by admin <?= $purchase->user['name'] ?>
                            <?php else: ?>
                                Purchase made online by <?= $purchase->user['name'] ?>
                            <?php endif; ?>
                        </li>
                        <?php if ($purchase->refunded): ?>
                            <li>
                                Marked <strong>refunded</strong> by
                                <?php if ($purchase->refunder['name']): ?>
                                    <?= $purchase->refunder['name'] ?>
                                <?php else: ?>
                                    an unknown user
                                <?php endif; ?>
                                on
                                <?= $purchase->refunded->format('F j, Y') ?>
                            </li>
                        <?php endif; ?>
                        <?php if ($purchase->notes): ?>
                            <li>
                                <?= nl2br($purchase->notes) ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->element('pagination') ?>

<?php $this->Html->script('admin', ['block' => 'scriptBottom']); ?>
<?php $this->append('buffered'); ?>
    adminPurchasesIndex.init();
<?php $this->end(); ?>