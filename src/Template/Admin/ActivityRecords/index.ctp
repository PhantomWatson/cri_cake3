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

<?= $this->element('pagination') ?>

<table class="table" id="activity-records">
    <thead>
        <tr>
            <th>
                Activity
            </th>
            <th>
                Community
            </th>
            <th>
                User
            </th>
            <th>
                Time
            </th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($activityRecords as $activityRecord): ?>
            <tr>
                <td>
                    <?= $this->ActivityRecords->event($activityRecord) ?>
                </td>
                <td>
                    <?= $this->ActivityRecords->community($activityRecord) ?>
                </td>
                <td>
                    <?= $this->ActivityRecords->user($activityRecord) ?>
                </td>
                <td>
                    <?= $this->Time->format($activityRecord->created, 'MMM d Y, h:mma', false, 'America/New_York') ?>
                </td>
            </tr>
            <tr class="details">
                <td colspan="4">
                    <?php $details = $this->ActivityRecords->details($activityRecord); ?>
                    <?php if ($details): ?>
                        <div>
                            <?= $details ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->element('pagination') ?>

<p>
    <?php if ($this->request->query('show-dummy')): ?>
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

<?php $this->append('buffered'); ?>
    activityRecords.init();
<?php $this->end(); ?>
