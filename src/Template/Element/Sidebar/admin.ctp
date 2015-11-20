<ul>
    <li class="link">
        <?= $this->Html->link(
            'Admin Guide',
            [
                'prefix' => 'admin',
                'controller' => 'Pages',
                'action' => 'guide'
            ]
        ) ?>
    </li>
    <li class="link">
        <?= $this->Html->link(
            'Manage Users',
            [
                'prefix' => 'admin',
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
                'controller' => 'Communities',
                'action' => 'index'
            ]
        ) ?>
    </li>
    <li class="link">
        <?= $this->Html->link(
            'Payment Records',
            [
                'prefix' => 'admin',
                'controller' => 'Purchases',
                'action' => 'index'
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
</ul>