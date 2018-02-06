<p>
    <?= $userName ?>,
</p>

<p>
    <?= $communityName ?> has been added to the CRI website, but has not yet been assigned any clients.
    Once it is known who will be representing this community, please
    <a href="<?= $actionUrl ?>">create their client accounts</a>.
</p>

<?= $this->element('Email/html/admin_email_settings_link') ?>
