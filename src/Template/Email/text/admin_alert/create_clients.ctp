<?php
/**
 * @var \App\View\AppView $this
 * @var string $actionUrl
 * @var string $communityName
 * @var string $userName
 */
/**
 * @var \App\View\AppView $this
 */
?>
<?= $userName ?>,

<?= $communityName ?> has been added to the CRI website, but has not yet been assigned any clients. Once it is known who will be representing this community, please create their client accounts by visiting <?= $actionUrl ?>.

<?= $this->element('Email/text/admin_email_settings_link') ?>
