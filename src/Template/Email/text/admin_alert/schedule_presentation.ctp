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
<?= $userName ?>,

Presentation <?= strtoupper($presentationLetter) ?> materials for <?= $communityName ?> have been delivered from CBER to ICI. The next step in the CRI process for that community is for ICI to schedule Presentation <?= strtoupper($presentationLetter) ?>: <?= $actionUrl ?>

<?= $this->element('Email/text/admin_email_settings_link') ?>
