<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Community $community
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php if ($currentlyActive): ?>
    <p class="alert alert-success">
        Your account has been successfully reactivated.
    </p>
    <p>
        <?= $this->Html->link(
            'Go to Client Home',
            [
                'prefix' => 'client',
                'controller' => 'Communities',
                'action' => 'index'
            ],
            ['class' => 'btn btn-primary']
        ) ?>
    </p>
<?php else: ?>
    <p class="alert alert-info">
        Your account has been deactivated due to inactivity. If you would like to resume your participation in the
        Community Readiness Initiative, simply click the following button.
    </p>
    <?php
        echo $this->Form->create($community);
        echo $this->Form->button(
            'Reactivate account',
            ['class' => 'btn btn-primary']
        );
        echo $this->Form->end();
    ?>
<?php endif; ?>
