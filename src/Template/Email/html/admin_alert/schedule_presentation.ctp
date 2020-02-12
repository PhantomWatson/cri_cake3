<?php
/**
 * @var \App\View\AppView $this
 * @var string $actionUrl
 * @var string $communityName
 * @var mixed $presentationLetter
 * @var string $userName
 */
/**
 * @var \App\View\AppView $this
 */
?>
<p>
    <?= $userName ?>,
</p>

<p>
    Presentation <?= strtoupper($presentationLetter) ?> materials for <?= $communityName ?> have been delivered from CBER to ICI.
    The next step in the CRI process for that community is for ICI to
    <a href="<?= $actionUrl ?>">schedule Presentation <?= strtoupper($presentationLetter) ?></a>.
</p>

<?= $this->element('Email/html/admin_email_settings_link') ?>
