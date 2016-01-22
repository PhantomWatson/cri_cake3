<p>
    <?= $user->name ?>,
</p>

<p>
    Thank you for enrolling your community in the <a href="<?= $homeUrl ?>">Community Readiness Initiative</a>. A new account has been created so that you can enter information needed for the next step in your community's CRI process.
</p>

<p>
    <a href="<?= $loginUrl ?>">Log in to the CRI website</a> using the following information:
</p>

<ul>
    <li>
        Email: <?= $user->email ?>
    </li>
    <li>
        Password: <?= $password ?>
    </li>
</ul>

<p>
    Once logged in, you can change your password and begin the next stage of CRI.
</p>

<p>
    If you have any questions, please email <a href="mailto:cri@bsu.edu">cri@bsu.edu</a>.
</p>

<?= $this->element('Email'.DS.'html'.DS.'signature') ?>
