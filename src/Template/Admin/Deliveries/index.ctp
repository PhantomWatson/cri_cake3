<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Delivery $deliveries
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
        'Report a Delivery',
        [
            'prefix' => 'admin',
            'controller' => 'Deliveries',
            'action' => 'add'
        ],
        [
            'class' => 'btn btn-success'
        ]
    ) ?>
</p>

<?php if ($deliveries->isEmpty()): ?>
    <p class="alert alert-info">
        No deliveries have been reported
    </p>
<?php else: ?>
    <?= $this->element('deliveries') ?>
<?php endif; ?>

<p>
    <?php if ($this->request->getQuery('show-dummy')): ?>
        <?= $this->Html->link(
            'Hide deliveries for dummy communities',
            ['?' => ['show-dummy' => 0]]
        ) ?>
    <?php else: ?>
        <?= $this->Html->link(
            'Show deliveries for dummy communities',
            ['?' => ['show-dummy' => 1]]
        ) ?>
    <?php endif; ?>
</p>
