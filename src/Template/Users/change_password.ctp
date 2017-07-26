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

<p>
    <strong>Strong passwords</strong> are at least six characters long and include lowercase and uppercase letters, as well as numbers and symbols.
    We recommend using strong passwords to make it more difficult for anyone to gain entry to your account using an automated
    password-guessing script.
</p>

<?php
    echo $this->Form->create($user, [
        'valueSources' => []
    ]);
    echo $this->Form->input(
        'new_password',
        [
            'autocomplete' => 'off',
            'class' => 'form-control',
            'div' => ['class' => 'form-group'],
            'label' => 'Change password',
            'type' => 'password'
        ]
    );
    echo $this->Form->input(
        'confirm_password',
        [
            'autocomplete' => 'off',
            'class' => 'form-control',
            'div' => ['class' => 'form-group'],
            'label' => 'Repeat new password',
            'type' => 'password'
        ]
    );
    echo $this->Form->button(
        'Submit',
        ['class' => 'btn btn-primary']
    );
    echo $this->Form->end();
