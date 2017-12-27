<?php
/**
 * @var \App\View\AppView $this
 */
?>
<li class="link">
    <?= $this->Html->link(
        'Admin To-Do',
        [
            'prefix' => 'admin',
            'plugin' => false,
            'controller' => 'Communities',
            'action' => 'toDo'
        ]
    ) ?>
</li>
<li class="link">
    <?= $this->Html->link(
        'Reports',
        [
            'prefix' => 'admin',
            'plugin' => false,
            'controller' => 'Reports',
            'action' => 'index'
        ]
    ) ?>
</li>
<li class="link">
    <?= $this->Html->link(
        'Manage Users',
        [
            'prefix' => 'admin',
            'plugin' => false,
            'controller' => 'Users',
            'action' => 'index'
        ]
    ) ?>
</li>
<li class="link">
    <?= $this->Html->link(
        'Manage Communities',
        [
            'prefix' => 'admin',
            'plugin' => false,
            'controller' => 'Communities',
            'action' => 'index'
        ]
    ) ?>
    <?= $this->element('Sidebar/admin_community') ?>
</li>
<li class="link">
    <?= $this->Html->link(
        'Payment Records',
        [
            'prefix' => 'admin',
            'plugin' => false,
            'controller' => 'Purchases',
            'action' => 'index'
        ]
    ) ?>
</li>
<li class="link">
    <?= $this->Html->link(
        'Deliverables',
        [
            'prefix' => 'admin',
            'plugin' => false,
            'controller' => 'Deliveries',
            'action' => 'index'
        ]
    ) ?>
</li>
<li class="link">
    <?= $this->Html->link(
        'Alignment Calculation Settings',
        [
            'prefix' => 'admin',
            'plugin' => false,
            'controller' => 'Communities',
            'action' => 'alignmentCalcSettings'
        ]
    ) ?>
</li>
<li class="link">
    <?= $this->Html->link(
        'Activity Log',
        [
            'prefix' => 'admin',
            'plugin' => false,
            'controller' => 'ActivityRecords',
            'action' => 'index'
        ]
    ) ?>
</li>
<li class="link">
    <?= $this->Html->link(
        'Admin Guide',
        [
            'prefix' => 'admin',
            'plugin' => false,
            'controller' => 'Pages',
            'action' => 'guide'
        ]
    ) ?>
</li>
