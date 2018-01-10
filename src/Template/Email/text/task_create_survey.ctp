<?= $userName ?>,

<?= $communityName ?> has been promoted to Step <?= $toStep ?> of CRI. The next step in the CRI process for that community is for ICI to create a community <?= $newSurveyType ?> questionnaire in SurveyMonkey (https://www.surveymonkey.com/) for <?= $communityName ?>.

Once this is done, link the community's CRI account to the new questionnaire by visiting <?= $actionUrl ?>.

<?= $this->element('Email/text/admin_email_settings_link') ?>
