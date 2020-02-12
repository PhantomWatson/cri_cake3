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

<?= $communityName ?> is now ready for ICI to create a community <?= $surveyType ?>s questionnaire in SurveyMonkey (https://www.surveymonkey.com/).

Once this is done, please link the community's CRI account to the new questionnaire by visiting <?= $actionUrl ?>.

<?= $this->element('Email/text/admin_email_settings_link') ?>
