<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Community[]|\Cake\Collection\CollectionInterface $communities
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p class="alert alert-info">
    Need to mark a community as inactive (dropped out)? From the
    <?= $this->Html->link(
        'Manage Communities',
        [
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'index'
        ]
    ) ?>
    page, find the <em>Actions</em> menu for the correct community, open it, and then select <em>Deactivate</em>.
</p>

<table class="table" id="admin-to-do">
    <thead>
        <tr>
            <th>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        Responsible Party
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <?= $this->Html->link(
                                'All',
                                [
                                    'prefix' => 'admin',
                                    'controller' => 'Communities',
                                    'action' => 'toDo'
                                ]
                            ) ?>
                        </li>
                        <?php foreach (['CBER', 'ICI', 'Client'] as $responsibleParty): ?>
                            <li>
                                <?= $this->Html->link(
                                    $responsibleParty,
                                    [
                                        'prefix' => 'admin',
                                        'controller' => 'Communities',
                                        'action' => 'toDo',
                                        '?' => [
                                            'responsible' => $responsibleParty
                                        ]
                                    ]
                                ) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </th>
            <th>
                Community
            </th>
            <th>
                To Do
            </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($communities as $community): ?>
            <tr>
                <td class="<?= $community->toDo['class'] ?>">
                    <?php
                        switch ($community->toDo['class']) {
                            case 'ready':
                                $icon = 'exclamation-sign';
                                break;
                            case 'waiting':
                                $icon = 'hourglass';
                                break;
                            case 'complete':
                            default:
                                $icon = 'check';
                                break;
                        }
                    ?>
                    <span class="glyphicon glyphicon-<?= $icon ?>"></span>
                    <?php
                        $partyLinks = [];
                        if ($community->toDo['responsible']) {
                            foreach ($community->toDo['responsible'] as $responsibleParty) {
                                $partyLinks[] = $this->Html->link(
                                    $responsibleParty,
                                    [
                                        'prefix' => 'admin',
                                        'controller' => 'Communities',
                                        'action' => 'toDo',
                                        '?' => [
                                            'responsible' => $responsibleParty
                                        ]
                                    ],
                                    ['title' => 'View only this party\'s tasks']
                                );
                            }
                        }
                        echo implode(' / ', $partyLinks);
                    ?>
                </td>
                <td>
                    <?= $community->name ?>
                </td>
                <td class="<?= $community->toDo['class'] ?>">
                    <?= $community->toDo['msg'] ?>
                    <?php if (isset($community->toDo['since'])): ?>
                        for <?= $community->toDo['since'] ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
