<p>
    <?= $userName ?>,
</p>

<p>
    <?= $communityName ?> is now ready for ICI to
    <a href="https://www.surveymonkey.com/">create a community <?= $surveyType ?>s questionnaire in SurveyMonkey</a>.
</p>

<p>
    Once this is done, please <a href="<?= $actionUrl ?>">link the community's CRI account to the new questionnaire</a>.
</p>

<?= $this->element('Email/html/admin_email_settings_link') ?>
