<?php
/**
 * @var string[] $sentEmails
 * @var string[] $skippedEmails
 * @var \App\View\AppView $this
 */
?>

<?php if ($sentEmails): ?>
    <?= sprintf(
        ' Alert %s sent to %s.',
        __n('email', 'emails', count($sentEmails)),
        implode(', ', $sentEmails)
    ) ?>
<?php endif; ?>

<?php if ($skippedEmails): ?>
    <?= sprintf(
        ' Skipping sending %s to %s (%s alerted < 2 days ago).',
        __n('email', 'emails', count($skippedEmails)),
        implode(', ', $skippedEmails),
        __n('was', 'were', count($skippedEmails))
    ) ?>
<?php endif; ?>

