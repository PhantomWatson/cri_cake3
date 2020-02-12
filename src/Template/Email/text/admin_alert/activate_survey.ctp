<?php
/**
 * @var \App\View\AppView $this
 * @var string $actionUrl
 * @var string $communityName
 * @var string $surveyType
 * @var string $userName
 */
/**
 * @var \App\View\AppView $this
 */
?>
<?= $userName ?>,

<?= $communityName ?>'s community <?= $surveyType ?>s questionnaire has been created and linked to that community's CRI account, but has not yet been activated. Please activate this questionnaire by visiting <?= $actionUrl ?> so that invitations can be sent out and responses collected through the CRI website.

<?= $this->element('Email/text/admin_email_settings_link') ?>
