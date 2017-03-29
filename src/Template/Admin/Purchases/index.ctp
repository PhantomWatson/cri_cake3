<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?= $this->Html->link(
        'Add Payment Record',
        [
            'prefix' => 'admin',
            'controller' => 'Purchases',
            'action' => 'add'
        ],
        ['class' => 'btn btn-success']
    ) ?>
    <?= $this->Html->link(
        'OCRA Funding',
        [
            'prefix' => 'admin',
            'controller' => 'Purchases',
            'action' => 'ocra'
        ],
        ['class' => 'btn btn-default']
    ) ?>
</p>

<p>
    This is a list of all CRI product purchase records, including purchases made online through
    the CASHNet payment system by clients and payment records added manually by administrators.
</p>

<p>
    <strong>Refunds:</strong> If a refund is issued to a client, click the [Refund] button next to that purchase to
    record the refund. If a refund is not recorded, the CRI website will treat the client
    as if they have purchased that product and behave accordingly. Note that the [Refund]
    button does not actually issue a refund, only record that a refund has been issued.
</p>

<?= $this->element('pagination') ?>

<table class="table" id="purchases_index">
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
            <th>
                Report Refund
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
                    <?= $this->Time->format($purchase->created, 'M/d/YYYY', false, 'America/New_York'); ?>
                </td>
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
                    <?php if ($purchase->refunded): ?>
                        <button class="refunded btn btn-default btn-block">
                            Refunded
                        </button>
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
                    <button class="details btn btn-default btn-block">
                        Details
                    </button>
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
                        <li>
                            Funding source:
                            <?= $purchase->source ? $sources[$purchase->source] : 'Unknown' ?>
                        </li>
                        <?php if ($purchase->refunded): ?>
                            <li>
                                Marked refunded by
                                <?php if ($purchase->refunder['name']): ?>
                                    <?= $purchase->refunder['name'] ?>
                                <?php else: ?>
                                    an unknown user
                                <?php endif; ?>
                                on
                                <?= $this->Time->format($purchase->refunded, 'MMMM d, Y', false, 'America/New_York'); ?>
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

<?php $this->append('buffered'); ?>
    adminPurchasesIndex.init();
<?php $this->end(); ?>
