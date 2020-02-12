<?php
/**
 * @var \App\View\AppView $this
 * @var string $email
 * @var string $homeUrl
 * @var string $loginUrl
 * @var string $name
 * @var string $password
 * @var mixed $role
 */
?>
<p>
    <?= $name ?>,
</p>

<p>
    <?php if ($role == 'client'): ?>
        Thank you for enrolling your community in the <a href="<?= $homeUrl ?>">Community Readiness Initiative</a>. A
        new account has been created so that you can log in and manage your community's participation in the CRI
        process.
    <?php elseif ($role == 'admin'): ?>
        Your <a href="<?= $homeUrl ?>">Community Readiness Initiative</a> administrator account has been created.
    <?php endif; ?>
</p>

<p>
    You can now <a href="<?= $loginUrl ?>">log in to the CRI website</a> using the following information:
</p>

<ul>
    <li>
        Email: <?= $email ?>
    </li>
    <li>
        Password: <?= $password ?>
    </li>
</ul>

<p>
    It is recommended that you change your password after logging in.
    <?php if ($role == 'client'): ?>
        Once logged in, access the
        <?= $this->Html->link(
            'Client Home',
            [
                'prefix' => 'client',
                'controller' => 'Communities',
                'action' => 'index'
            ]
        ) ?>
        page to make payments, distribute questionnaire invitations, and check your community's progress.
    <?php elseif ($role == 'admin'): ?>
        Once logged in, you can access the website's various administrative functions.
    <?php endif; ?>
</p>

<p>
    If you have any questions, please email <a href="mailto:cri@bsu.edu">cri@bsu.edu</a>.
</p>
