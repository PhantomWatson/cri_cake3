<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    Have you forgotten the password that you use to log in to your CRI account?
    In the field below, enter the email address that is associated with your account,
    and we'll email you a link that you can use for the next 24 hours to reset your password.
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
    echo $this->Form->button(
        'Reset Password',
        ['class' => 'btn btn-primary']
    );
    echo $this->Form->end();
?>
