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
            <th>
                Report Refund
            </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($purchases as $purchase): ?>
            <tr>
                <td>
                    <?= $purchase['User']['name'] ?>
                </td>
                <td>
                    <?php if ($purchase['Product']['description']): ?>
                        <?= $purchase['Product']['description'] ?>
                    <?php else: ?>
                        <span class="unknown">
                            Unknown product
                        </span>
                    <?php endif; ?>

                    <?php if ($purchase['Product']['price']): ?>
                        ($<?= number_format($purchase['Product']['price'] / 100) ?>)
                    <?php endif; ?>

                    <?php if ($purchase['Purchase']['refunded']): ?>
                        <p class="refunded">
                            Marked <strong>refunded</strong> by
                            <?php if ($purchase['Refunder']['name']): ?>
                                <?= $purchase['Refunder']['name'] ?>
                            <?php else: ?>
                                an unknown user
                            <?php endif; ?>
                            on
                            <?php
                                $timestamp = strtotime($purchase['Purchase']['refunded']);
                                echo date('F j, Y', $timestamp);
                            ?>
                        </p>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                        $timestamp = strtotime($purchase['Purchase']['created']);
                        echo date('F j, Y', $timestamp);
                    ?>
                </td>
                <td>
                    <?php if (! $purchase['Purchase']['refunded']): ?>
                        <?= $this->Form->postLink(
                            '<span class="glyphicon glyphicon-usd"></span><span class="glyphicon glyphicon-share-alt"></span>',
                            [
                                'prefix' => 'admin',
                                'action' => 'refund',
                                $purchase['Purchase']['id']
                            ],
                            [
                                'class' => 'btn btn-default',
                                'escape' => false
                            ],
                            'Are you sure you want to mark this payment as having been refunded?'
                        ) ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->element('pagination') ?>