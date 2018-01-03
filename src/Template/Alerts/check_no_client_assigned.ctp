<?php
/**
 * @var \Cake\ORM\Query $communities
 * @var string[] $sentEmails
 * @var string[] $skippedEmails
 */
use Cake\Utility\Hash;
?>
<?php if ($communities->isEmpty()): ?>
    No communities with officials surveys have been added more than two hours ago that lack clients.
<?php else: ?>
    These communities with officials surveys have been added more than two hours ago and lack clients:
    <?php
        $communityNames = Hash::extract($communities->toArray(), '{n}.name');
        echo implode(', ', $communityNames) . '.';
    ?>
    <?php if ($sentEmails): ?>
        <?= sprintf(
            ' Alert %s sent to %s.',
            __n('email', 'emails', count($sentEmails)),
            implode($sentEmails)
        ) ?>
    <?php endif; ?>

    <?php if ($skippedEmails): ?>
        <?= sprintf(
            ' Skipping sending %s to %s (%s alerted < 2 hours ago).',
            __n('email', 'emails', count($skippedEmails)),
            implode(', ', $skippedEmails),
            __n('was', 'were', count($skippedEmails))
        ) ?>
    <?php endif; ?>
<?php endif; ?>
