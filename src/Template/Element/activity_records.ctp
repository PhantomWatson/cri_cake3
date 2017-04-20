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

<?php $this->append('buffered'); ?>
    activityRecords.init();
<?php $this->end(); ?>
