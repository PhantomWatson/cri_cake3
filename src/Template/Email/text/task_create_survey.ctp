<?= $userName ?>,

<?= $communityName ?> is now ready for ICI to create a community <?= $newSurveyType ?> questionnaire in SurveyMonkey (https://www.surveymonkey.com/) for <?= $communityName ?>.

Once this is done, please link the community's CRI account to the new questionnaire by visiting <?= $actionUrl ?>.

<?= $this->element('Email/text/admin_email_settings_link') ?>
