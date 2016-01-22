<?= $user->name ?>,

Your new account on the Community Readiness Initiative website (<?= $homeUrl ?>) has been created.

You can now log in to the CRI website at <?= $loginUrl ?> using the following information:
- Email: <?= $user->email."\n" ?>
- Password: <?= $password."\n" ?>

Once logged in, you can change your password. If you have any questions, please email cri@bsu.edu.


<?= $this->element('Email'.DS.'text'.DS.'signature') ?>
