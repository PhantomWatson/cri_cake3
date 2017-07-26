<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Survey $survey
 * @var \App\Model\Entity\Community $community
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php if ($step == 'input'): ?>
    <?php
        echo $this->Form->create();
        echo $this->Form->input(
            'surveyId',
            ['type' => 'number']
        );
        echo $this->Form->input(
            'userId',
            [
                'label' => 'User ID of sender',
                'type' => 'number'
            ]
        );
        echo $this->Form->button(
            'Review details before sending',
            ['class' => 'btn btn-default']
        );
        echo $this->Form->end();
    ?>
<?php elseif ($step == 'confirm'): ?>
    <p>
        Please review the following and confirm.
    </p>
    <p>
        Survey: Community <?= ucwords($survey->type) ?>s (ID #<?= $survey->id ?>)
        <br />
        Community: <?= $community->name ?>
        <br />
        Sender: <?= $sender->name ?> &lt;<?= $sender->email ?>&gt;
        <br />
        Recipients: <?= empty($recipients) ? 'None' : null ?>
    </p>
    <?php if ($recipients): ?>
        <ul>
            <?php foreach ($recipients as $email): ?>
                <li>
                    <?= $email ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php
        echo $this->Form->create();
        echo $this->Form->hidden('surveyId');
        echo $this->Form->hidden('userId');
        echo $this->Form->hidden('confirmed', ['value' => true]);
        echo $this->Form->button(
            'Resend invitations',
            ['class' => 'btn btn-primary']
        );
        echo $this->Form->end();
    ?>
<?php elseif ($step == 'results'): ?>
    <div class="alert alert-<?= (bool)$result ? 'success' : 'danger' ?>">
        <strong>
            <?= ((bool)$result ? 'Success' : 'Error') ?>
        </strong>
        <br />
        Results:
        <?php var_dump($result) ?>
    </div>
<?php endif; ?>
