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

<?= $this->element('Purchases/table') ?>
