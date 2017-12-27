<?php
/**
 * @var \App\View\AppView $this
 */

/*
    <h2> deliberately omitted so the admin block can insert a <select> between it and <ul>
*/
?>

<li class="link client_home">
    <?= $this->Html->link(
        'Client Home',
        [
            'prefix' => 'client',
            'plugin' => false,
            'controller' => 'Communities',
            'action' => 'index'
        ]
    ) ?>
</li>
<?php if ($authUser && $authUser['role'] == 'client'): ?>
    <li class="link">
        <?= $this->Html->link(
            'Purchases',
            [
                'prefix' => 'client',
                'plugin' => false,
                'controller' => 'Purchases',
                'action' => 'index'
            ]
        ) ?>
    </li>
    <li class="link">
        <?= $this->Html->link(
            'Update Contact Info',
            [
                'prefix' => false,
                'plugin' => false,
                'controller' => 'Users',
                'action' => 'updateContact'
            ]
        ) ?>
    </li>
    <li class="link">
        <?= $this->Html->link(
            'Change Password',
            [
                'prefix' => false,
                'plugin' => false,
                'controller' => 'Users',
                'action' => 'changePassword'
            ]
        ) ?>
    </li>
    <li class="link">
        <?= $this->Html->link(
            'Logout',
            [
                'prefix' => false,
                'plugin' => false,
                'controller' => 'Users',
                'action' => 'logout'
            ]
        ) ?>
    </li>
<?php endif; ?>
