<?php
/**
 * @var \App\View\AppView $this
 * @var string $titleForLayout
 * @var mixed $trackedEvents
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p id="activity-records-intro">
    The following is a record of activity on the CRI website. If an activity has more details, click on the link in the
    "Activity" column to view them.
    <button class="btn btn-sm btn-link">
        What activities are tracked?
    </button>
</p>

<div id="activities-tracked">
    Tracked activities:
    <ul>
        <?php foreach ($trackedEvents as $event): ?>
            <li>
                <?= $event ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?= $this->element('activity_records') ?>

<p>
    <?php if ($this->request->getQuery('show-dummy')): ?>
        <?= $this->Html->link(
            'Hide activities for dummy communities',
            ['?' => ['show-dummy' => 0]]
        ) ?>
    <?php else: ?>
        <?= $this->Html->link(
            'Show activities for dummy communities',
            ['?' => ['show-dummy' => 1]]
        ) ?>
    <?php endif; ?>
</p>
