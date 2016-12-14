<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php if ($this->request->prefix == 'admin'): ?>
    <?php $this->element('script', ['script' => 'admin']); ?>
<?php endif; ?>

<p>
    <?php if ($this->request->prefix != 'admin'): ?>
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
    <?php endif; ?>
</p>

<?php if (empty($unresponsive)): ?>
    <p class="alert alert-success">
        Good news! Everyone who has been sent an invitation to participate
        in this questionnaire has submitted a response, so no reminders
        are necessary.
    </p>
<?php else: ?>
    <ul id="reminders">
        <li>
            Sending a reminder will re-send questionnaire invitation emails.
        </li>
        <li>
            <button id="toggleUnresponsiveList" class="btn btn-default btn-sm">
                <?= $unresponsiveCount ?> <?= __n('person', 'people', $unresponsiveCount) ?>
            </button>
            <?= __n('hasn\'t', 'haven\'t', $unresponsiveCount) ?>
            responded to this questionnaire yet.
            <div class="well" id="unresponsiveList">
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
        </li>
        <li>
            <?php if ($survey->reminder_sent): ?>
                A reminder was last sent for this questionnaire on
                <strong>
                    <?= $survey->reminder_sent->format('F j, Y') ?>.
                </strong>
            <?php else: ?>
                No reminder has been sent for this questionnaire yet.
            <?php endif; ?>
        </li>
    </ul>

    <p>
        <?= $this->Form->postLink(
            $survey->reminder_sent ? 'Send another reminder' : 'Send reminder',
            [
                'controller' => 'Surveys',
                'action' => 'remind',
                $this->request->prefix == 'admin' ? $survey->id : $survey->type
            ],
            ['class' => 'btn btn-primary']
        ) ?>
    </p>

    <?php $this->append('buffered'); ?>
        $('#reminders #toggleUnresponsiveList').click(function (event) {
            event.preventDefault();
            $('#unresponsiveList').slideToggle();
        });
    <?php $this->end(); ?>
<?php endif; ?>
