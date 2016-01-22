<p>
    <?= $user->name ?>,
</p>

<p>
    Your new account on the <a href="<?= $homeUrl ?>">Community Readiness Initiative website</a> has been created.
</p>

<p>
    You can now <a href="<?= $loginUrl ?>">log in to the CRI website</a> using the following information:
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
    Once logged in, you can change your password. If you have any questions, please email <a href="mailto:cri@bsu.edu">cri@bsu.edu</a>.
</p>

<?= $this->element('Email'.DS.'html'.DS.'signature') ?>
