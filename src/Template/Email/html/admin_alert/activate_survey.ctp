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
<p>
    <?= $userName ?>,
</p>

<p>
    <?= $communityName ?>'s community <?= $surveyType ?>s questionnaire has been created and linked to that community's
    CRI account, but has not yet been activated. Please <a href="<?= $actionUrl ?>">activate this questionnaire</a> so
    that invitations can be sent out and responses collected through the CRI website.
</p>

<?= $this->element('Email/html/admin_email_settings_link') ?>
