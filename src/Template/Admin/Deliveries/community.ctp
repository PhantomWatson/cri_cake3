<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Delivery $deliveries
 * @var mixed $communityId
 * @var string $titleForLayout
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-arrow-left"></span> Back to all communities',
        [
            'prefix' => 'admin',
            'controller' => 'Deliveries',
            'action' => 'index'
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>

    <?= $this->Html->link(
        'Report a Delivery',
        [
            'prefix' => 'admin',
            'controller' => 'Deliveries',
            'action' => 'add',
            $communityId
        ],
        [
            'class' => 'btn btn-success'
        ]
    ) ?>
</p>

<?php if ($deliveries->isEmpty()): ?>
    <p class="alert alert-info">
        No deliveries have been reported for this community
    </p>
<?php else: ?>
    <?= $this->element('deliveries') ?>
<?php endif; ?>
