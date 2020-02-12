<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Delivery $delivery
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
        '<span class="glyphicon glyphicon-arrow-left"></span> Back to Deliverables',
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
</p>

<?= $this->Form->create($delivery, [
    'id' => 'report-delivery-form'
]) ?>

<?= $this->Form->input('deliverable_id', [
    'label' => 'What was delivered?'
]) ?>

<?= $this->Form->input('community_id', [
    'label' => 'What community was it delivered for?'
]) ?>

<?= $this->Form->button(
    'Record Delivery',
    ['class' => 'btn btn-primary']
) ?>

<?= $this->Form->end() ?>
