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

<?= $communityName ?> is ready for CBER to deliver its Presentation <?= strtoupper($presentationLetter) ?> materials to ICI.

Once this is done, please report those materials delivered by visiting <?= $actionUrl ?>.

<?= $this->element('Email/text/admin_email_settings_link') ?>
