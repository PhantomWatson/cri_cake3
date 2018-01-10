<p>
    <?= $userName ?>,
</p>

<p>
    Presentation <?= $presentationLetter ?> has been purchased for <?= $communityName ?>. The next step in the CRI
    process for that community is for CBER to prepare and deliver Presentation <?= $presentationLetter ?> materials to
    ICI.
</p>

<p>
    Once this is done, please <a href="<?= $actionUrl ?>">report those materials delivered</a>.
</p>

<?= $this->element('Email/html/admin_email_settings_link') ?>
