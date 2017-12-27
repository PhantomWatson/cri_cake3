<?php
    /**
     * @var \App\View\AppView $this
     * @var \App\Model\Entity\User $user
     * @var bool $hasPasswordErrors
     */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?= $this->Form->create($user, ['id' => 'my-account']) ?>

<section>
    <h2>
        Update Contact Info
    </h2>
    <?php
        echo $this->Form->control(
            'name',
            [
                'class' => 'form-control',
                'div' => ['class' => 'form-group']
            ]
        );
        echo $this->Form->control(
            'email',
            [
                'class' => 'form-control',
                'div' => ['class' => 'form-group']
            ]
        );
    ?>
</section>

<?php if ($user['role'] == 'admin'): ?>
    <section>
        <h2>
            Admin Email Opt-in
        </h2>
        <p>
            Here, you can opt in or out of receiving emails about admin tasks that need to be addressed by ICI and/or
            CBER.
        </p>
        <?= $this->Form->control('ici_email_optin', [
            'label' => 'Indiana Communities Institute tasks'
        ]) ?>
        <?= $this->Form->control('cber_email_optin', [
            'label' => 'Center for Business and Economic Research tasks'
        ]) ?>
    </section>
<?php endif; ?>

<section class="password <?= $hasPasswordErrors ? 'active' : null ?>">
    <h2>
        <button class="btn btn-link">
            Change Password
        </button>
    </h2>
    <div>
        <p>
            <strong>Strong passwords</strong> are at least six characters long and include lowercase and uppercase letters,
            as well as numbers and symbols. We recommend using strong passwords to make it more difficult for anyone to gain
            entry to your account using an automated password-guessing script.
        </p>
        <?php
            echo $this->Form->control(
                'new_password',
                [
                    'autocomplete' => 'off',
                    'class' => 'form-control',
                    'div' => ['class' => 'form-group'],
                    'label' => 'Change password',
                    'type' => 'password'
                ]
            );
            echo $this->Form->control(
                'confirm_password',
                [
                    'autocomplete' => 'off',
                    'class' => 'form-control',
                    'div' => ['class' => 'form-group'],
                    'label' => 'Repeat new password',
                    'type' => 'password'
                ]
            );
        ?>
    </div>
</section>

<section>
    <?= $this->Form->button('Update', ['class' => 'btn btn-primary']) ?>
</section>

<?= $this->Form->end() ?>

<?php $this->Html->script('my-account', ['block' => 'scriptBottom']); ?>
<?php $this->append('buffered'); ?>
    myAccount.init();
<?php $this->end(); ?>
