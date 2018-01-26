<p>
    <?= $userName ?>,
</p>

<p>
    <?= $communityName ?>'s community <?= $surveyType ?>s survey has been closed. The next step in the CRI process for
    that community is for CBER to prepare and deliver Presentation <?= strtoupper($presentationLetter) ?> materials to
    ICI.
</p>

<p>
    Once this is done, please <a href="<?= $actionUrl ?>">report those materials delivered</a>.
</p>

<?= $this->element('Email/html/admin_email_settings_link') ?>
