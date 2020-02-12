<?php
/**
 * @var \App\View\AppView $this
 * @var array $community
 */
?>
<div class="dropdown">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        Actions <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" role="menu">
        <li>
            <?= $this->Html->link(
                '<span class="glyphicon glyphicon-tasks" aria-hidden="true"></span> ' .
                'Progress',
                [
                    'prefix' => 'admin',
                    'action' => 'progress',
                    $community['slug']
                ],
                ['escape' => false]
            ) ?>
        </li>
        <li>
            <?= $this->Html->link(
                '<span class="glyphicon glyphicon-calendar" aria-hidden="true"></span> ' .
                'Presentations',
                [
                    'prefix' => 'admin',
                    'action' => 'presentations',
                    $community['slug']
                ],
                ['escape' => false]
            ) ?>
        </li>
        <li>
            <?= $this->Html->link(
                '<span class="glyphicon glyphicon-user" aria-hidden="true"></span> ' .
                'Clients ('.count($community['clients']).')',
                [
                    'prefix' => 'admin',
                    'action' => 'clients',
                    $community['slug']
                ],
                ['escape' => false]
            ) ?>
        </li>
        <?php if (! empty($community['clients'])): ?>
            <li>
                <?= $this->Html->link(
                    '<span class="glyphicon glyphicon-home" aria-hidden="true"></span> ' .
                    'Client Home',
                    [
                        'prefix' => 'admin',
                        'action' => 'clienthome',
                        $community['slug']
                    ],
                    ['escape' => false]
                ) ?>
            </li>
        <?php endif; ?>

        <li>
            <?= $this->Html->link(
                '<span class="glyphicon glyphicon-stats" aria-hidden="true"></span> ' .
                'Performance Charts',
                [
                    'prefix' => false,
                    'action' => 'view',
                    $community['slug']
                ],
                ['escape' => false]
            ) ?>
        </li>
        <li>
            <?= $this->Html->link(
                '<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> ' .
                'Notes',
                [
                    'prefix' => 'admin',
                    'action' => 'notes',
                    $community['slug']
                ],
                ['escape' => false]
            ) ?>
        </li>
        <li>
            <?= $this->Html->link(
                '<span class="glyphicon glyphicon-usd" aria-hidden="true"></span> ' .
                'Purchases',
                [
                    'prefix' => 'admin',
                    'controller' => 'Purchases',
                    'action' => 'view',
                    $community['slug']
                ],
                ['escape' => false]
            ) ?>
        </li>
        <li>
            <?= $this->Html->link(
                '<span class="glyphicon glyphicon-list" aria-hidden="true"></span> ' .
                'Activity',
                [
                    'prefix' => 'admin',
                    'controller' => 'ActivityRecords',
                    'action' => 'community',
                    $community['id']
                ],
                ['escape' => false]
            ) ?>
        </li>
        <li>
            <?php
            $label =
                '<span class="glyphicon glyphicon-' .
                ($community['active'] ? 'remove-circle' : 'ok-circle') .
                '" aria-hidden="true"></span> ' .
                ($community['active'] ? 'Deactivate' : 'Reactivate');
            echo $this->Html->link(
                $label,
                [
                    'prefix' => 'admin',
                    'controller' => 'Communities',
                    'action' => 'activate',
                    $community['slug']
                ],
                ['escape' => false]
            );
            ?>
        </li>
        <li>
            <?= $this->Html->link(
                '<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> ' .
                'Edit Community',
                [
                    'prefix' => 'admin',
                    'action' => 'edit',
                    $community['slug']
                ],
                ['escape' => false]
            ) ?>
        </li>
        <li>
            <?= $this->Form->postLink(
                '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> ' .
                'Delete Community',
                [
                    'prefix' => 'admin',
                    'action' => 'delete',
                    $community['id']
                ],
                [
                    'confirm' => "Are you sure you want to delete {$community['name']}? " .
                        'This cannot be undone.',
                    'escape' => false
                ]
            ); ?>
        </li>
    </ul>
</div>
