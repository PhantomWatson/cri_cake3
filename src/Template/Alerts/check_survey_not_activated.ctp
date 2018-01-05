<?php
/**
 * @var \Cake\ORM\Query $communities
 */
use Cake\Utility\Hash;
?>
<?php if ($communities->isEmpty()): ?>
    No inactive surveys were created more than two hours ago that lack responses.
<?php else: ?>
    These communities have inactive surveys that lack responses:
    <?php
        $communityNames = Hash::extract($communities->toArray(), '{n}.name');
        echo implode(', ', $communityNames) . '.';
    ?>
    <?= $this->element('Alerts/email_results') ?>
<?php endif; ?>
