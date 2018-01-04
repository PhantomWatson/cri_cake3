<?php
/**
 * @var \Cake\ORM\Query $communities
 */
use Cake\Utility\Hash;
?>
<?php if ($communities->isEmpty()): ?>
    No active communities created more than two hours ago lack officials surveys.
<?php else: ?>
    These communities lack officials surveys:
    <?php
        $communityNames = Hash::extract($communities->toArray(), '{n}.name');
        echo implode(', ', $communityNames) . '.';
    ?>
    <?= $this->element('Alerts/email_results') ?>
<?php endif; ?>
