<?php /*
    <h2> deliberately omitted so the admin block can insert a <select> between it and <ul>
*/ ?>
<ul>
    <li class="link client_home">
        <?= $this->Html->link(
            'Client Home',
            [
                'prefix' => 'client',
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
                    'controller' => 'Users',
                    'action' => 'logout'
                ]
            ) ?>
        </li>
    <?php endif; ?>
</ul>
