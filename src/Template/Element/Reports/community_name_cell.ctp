<?php
/**
 * @var \App\View\AppView $this
 */
?>
<?= $community['name'] ?>

<?php if ($community['notes']): ?>
    <button type="button" class="notes" data-toggle="modal" data-target="#notes-modal" title="View notes" data-community-id="<?= $communityId ?>" data-community-name="<?= $community['name'] ?>">
        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
    </button>
<?php endif; ?>

<?php if ($community['recentActivity']): ?>
    <button type="button" class="recent-activity" data-toggle="modal" data-target="#notes-modal" title="View recent activity" data-community-id="<?= $communityId ?>" data-community-name="<?= $community['name'] ?>">
        <span class="glyphicon glyphicon-list" aria-hidden="true"></span>
    </button>
<?php endif; ?>

<br />

<span class="area-details">
    <?= $community['parentArea'] ?>
</span>

<?php if ($community['recentActivity']): ?>
    <?php
        $community['recentActivity'] = array_slice($community['recentActivity'], 0, 5);
        $count = count($community['recentActivity']);
    ?>
    <div class="recent-activity hidden-modal-content" data-community-id="<?= $communityId ?>">
        <p>
            <?php if ($count > 1): ?>
                The <?= $count ?> most recent updates
            <?php elseif ($count == 1): ?>
                The only update
            <?php endif; ?>
            to <?= $community['name'] ?> in the last 30 days:
        </p>
        <ul>
            <?php foreach ($community['recentActivity'] as $activityRecord): ?>
                <li>
                    <strong>
                        <?= $this->Time->format(
                            $activityRecord->created,
                            'MMM d Y, h:mma',
                            false,
                            'America/New_York'
                        ) ?>
                    </strong>
                    -
                    <?= $this->ActivityRecords->event($activityRecord) ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <p>
            <?= $this->Html->link(
                'View all activity associated with ' . $community['name'],
                [
                    'prefix' => 'admin',
                    'controller' => 'ActivityRecords',
                    'action' => 'community',
                    $communityId
                ]
            ) ?>
        </p>
    </div>
<?php endif; ?>
