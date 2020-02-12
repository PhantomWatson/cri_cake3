<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Community $community
 * @var mixed $currentlyActive
 * @var string $titleForLayout
 * @var string $warning
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php if ($warning): ?>
    <p class="alert alert-danger">
        Warning: <?= $warning ?>
    </p>
<?php endif; ?>

<h2>
    About Community Active/Inactive Status:
</h2>
<ul>
    <li>
        Communities should be marked "inactive" if their clients are no longer participating in CRI nor explicitly opting out of future participation.
    </li>
    <li>
        The
        <?= $this->Html->link(
            'Admin To-Do',
            [
                'prefix' => 'admin',
                'controller' => 'Communities',
                'action' => 'toDo'
            ]
        ) ?>
        list and
        <?= $this->Html->link(
            'Manage Communities',
            [
                'prefix' => 'admin',
                'controller' => 'Communities',
                'action' => 'index'
            ]
        ) ?>
        page will hide inactive communities by default.
    </li>
    <li>
        Administrators can reactivate inactive communities at any time through the Manage Communities page.
    </li>
    <li>
        Clients logging into accounts associated with inactive communities will be prompted
        to reactivate their communities before continuing.
    </li>
</ul>

<p>
    <?= $community['name'] ?> is currently
    <strong><?= $currentlyActive ? 'active' : 'inactive' ?></strong>.
</p>

<?php
    echo $this->Form->create($community);
    echo $this->Form->hidden(
        'active',
        ['value' =>  $currentlyActive ? 0 : 1]
    );
    echo $this->Form->button(
        $titleForLayout,
        [
            'class' => 'btn ' . ($currentlyActive ? 'btn-danger' : 'btn-success')
        ]
    );
    echo $this->Form->end();
