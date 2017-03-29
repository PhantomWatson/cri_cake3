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

<p class="well" id="total-ocra-payments">
    Total OCRA-designated payments:
    <strong>
        $<?= number_format($total) ?>
    </strong>
</p>

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
        <?php foreach ($purchases as $purchase): ?>
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

                    <?php if (! $purchase->redunded): ?>
                        <span class="label label-warning">
                            Refunded
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
