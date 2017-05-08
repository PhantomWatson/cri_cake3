<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php if ($this->request->prefix == 'admin'): ?>
    <?= $this->element('back_to_overview_link', [
        'communityId' => $community['id'],
        'surveyType' => $survey['type']
    ]) ?>
<?php else: ?>
    <p>
        <?= $this->Html->link(
            '<span class="glyphicon glyphicon-arrow-left"></span> Back to Client Home',
            [
                'prefix' => 'client',
                'controller' => 'Communities',
                'action' => 'index'
            ],
            [
                'class' => 'btn btn-default',
                'escape' => false
            ]
        ) ?>
    </p>
<?php endif; ?>

<p>
    If at least a week has passed since sending invitations to fill out this questionnaire,
    you can send reminder emails to any community <?= $survey->type ?>s who have not yet responded.
</p>

<?php if (empty($unresponsive)): ?>
    <p class="alert alert-success">
        Good news! Everyone who has been sent an invitation to participate
        in this questionnaire has submitted a response, so no reminders
        are necessary.
    </p>
<?php else: ?>
    <p>
        <strong>
            <?= $unresponsiveCount ?> <?= __n('person', 'people', $unresponsiveCount) ?>
        </strong>
        <?= __n('hasn\'t', 'haven\'t', $unresponsiveCount) ?>
        responded to this questionnaire yet.
        <button id="toggle-unresponsive-list" class="btn btn-default btn-sm">
            Who?
        </button>
    </p>

    <div class="well" id="unresponsive-list">
        <ul>
            <?php foreach ($unresponsive as $person): ?>
                <li>
                    <?php if ($person->name): ?>
                        <?= $person->name ?>
                    <?php else: ?>
                        (no name)
                    <?php endif; ?>

                    <?php if ($person->title): ?>
                        <span class="title">
                            (<?= $person->title ?>)
                        </span>
                    <?php endif; ?>

                    <a href="mailto:<?= $person->email ?>" class="email">
                        <?= $person->email ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <p>
        <?php if ($survey->reminder_sent): ?>
            A reminder was last sent for this questionnaire on
            <strong>
                <?= $this->Time->format($survey->reminder_sent, 'MMMM d, Y', false, 'America/New_York'); ?>.
            </strong>
        <?php else: ?>
            <strong>
                No reminders
            </strong>
            have been sent for this questionnaire yet.
        <?php endif; ?>
    </p>

    <p>
        <?php
            if ($unresponsiveCount == 1) {
                $label = $survey->reminder_sent ? 'Send another reminder' : 'Send reminder';
            } else {
                $label = $survey->reminder_sent ? 'Send more reminders' : 'Send reminders';
            }
            echo $this->Form->postLink(
                $label,
                [
                    'controller' => 'Surveys',
                    'action' => 'remind',
                    $this->request->prefix == 'admin' ? $survey->id : $survey->type
                ],
                ['class' => 'btn btn-primary']
            );
        ?>
    </p>

    <?php $this->append('buffered'); ?>
        $('#toggle-unresponsive-list').click(function (event) {
            event.preventDefault();
            $('#unresponsive-list').slideToggle();
        });
    <?php $this->end(); ?>
<?php endif; ?>
