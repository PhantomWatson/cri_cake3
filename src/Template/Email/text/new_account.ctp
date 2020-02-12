<?php
/**
 * @var \App\Model\Entity\User $user
 * @var \App\View\AppView $this
 * @var string $email
 * @var string $loginUrl
 * @var string $name
 * @var string $password
 * @var mixed $role
 */
?>
<?= $name ?>,

<?php if ($role == 'client'): ?>
    Thank you for enrolling your community in the Community Readiness Initiative. A new account has been created so that you can log in and manage your community's participation in the CRI process.
<?php elseif ($role == 'admin'): ?>
    Your Community Readiness Initiative administrator account has been created.
<?php endif; ?>

Log in to the CRI website at <?= $loginUrl ?> using the following information:
- Email: <?= $email."\n" ?>
- Password: <?= $password."\n" ?>

It is recommended that you change your password after logging in.

<?php if ($role == 'client'): ?>
    Once logged in, you can access the Client Home page to make payments, distribute questionnaire invitations, and check your community's progress.
<?php elseif ($role == 'admin'): ?>
    Once logged in, you can access the website's various administrative functions.
<?php endif; ?>

If you have any questions, please email cri@bsu.edu.
