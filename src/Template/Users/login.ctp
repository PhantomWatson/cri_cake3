<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p class="alert alert-info">
    <strong>
        Have you enrolled your community in CRI?
    </strong>
    If so, log in here to access your client account and check on your community's progress.
    If you haven't enrolled yet, please visit the
    <?= $this->Html->link(
        'CRI enrollment page',
        [
            'controller' => 'Pages',
            'action' => 'enroll'
        ]
    ) ?>. After we've created your account, you will be sent login info.
</p>

<?php
    echo $this->Form->create($user);
    echo $this->Form->input(
        'email',
        [
            'class' => 'form-control',
            'div' => ['class' => 'form-group']
        ]
    );
    echo $this->Form->input(
        'password',
        [
            'class' => 'form-control',
            'div' => ['class' => 'form-group']
        ]
    );
    echo $this->Form->input(
        'auto_login',
        [
            'label' => 'Keep me logged in on this computer',
            'type' => 'checkbox'
        ]
    );
    echo $this->Form->button(
        'Login',
        ['class' => 'btn btn-primary']
    );
    echo $this->Form->end();
?>

<p>
    <?= $this->Html->link(
        'I forgot my password',
        [
            'prefix' => false,
            'controller' => 'Users',
            'action' => 'forgotPassword'
        ]
    ) ?>
</p>
