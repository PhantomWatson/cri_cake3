<?php
/**
 * @var \Cake\ORM\Query $communities
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
    <?= $this->element('Alerts/email_results') ?>
<?php endif; ?>
