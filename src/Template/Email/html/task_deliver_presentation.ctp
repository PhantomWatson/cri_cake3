<p>
    <?= $userName ?>,
</p>

<p>
    <?= $communityName ?> is ready for CBER to deliver its Presentation <?= strtoupper($presentationLetter) ?>
    materials to ICI.
</p>

<p>
    Once this is done, please <a href="<?= $actionUrl ?>">report those materials delivered</a>.
</p>

<?= $this->element('Email/html/admin_email_settings_link') ?>
