<p>
    <?= $userName ?>,
</p>

<p>
    Presentation <?= $presentationLetter ?> materials for <?= $communityName ?> have been delivered from CBER to ICI.
    The next step in the CRI process for that community is for ICI to
    <a href="<?= $actionUrl ?>">schedule Presentation <?= $presentationLetter ?></a>.
</p>

<?= $this->element('Email/html/admin_email_settings_link') ?>
