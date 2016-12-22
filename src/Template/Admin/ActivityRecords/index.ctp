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
                    <?= $activityRecord->has('community') ? $activityRecord->community->name: ''; ?>
                </td>
                <td>
                    <?= $this->ActivityRecords->user($activityRecord) ?>
                </td>
                <td>
                    <?= $activityRecord->created->format('M j, Y - g:ia') ?>
                </td>
            </tr>
            <tr class="details">
                <td colspan="4">
                    <?= $this->ActivityRecords->details($activityRecord) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->element('pagination') ?>

<?php $this->append('buffered'); ?>
    activityRecords.init();
<?php $this->end(); ?>
