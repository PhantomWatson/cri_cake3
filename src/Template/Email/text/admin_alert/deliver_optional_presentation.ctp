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

Presentation <?= strtoupper($presentationLetter) ?> has been purchased for <?= $communityName ?>. The next step in the CRI process
for that community is for CBER to prepare and deliver Presentation <?= strtoupper($presentationLetter) ?> materials to ICI.

Once this is done, please report those materials delivered by visiting <?= $actionUrl ?>.

<?= $this->element('Email/text/admin_email_settings_link') ?>
