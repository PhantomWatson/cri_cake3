<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php if ($warning): ?>
    <p class="alert alert-warning">
        <?= $warning ?>
    </p>
<?php endif; ?>

<p>
    Questionnaires should be activated when communities enter the appropriate stage and deactivated as soon as
    response collection is considered complete.
</p>

<p>
    <strong>Active questionnaires</strong> have their responses automatically imported and allow invitations and reminders to
    be sent out.
</p>

<p>
    <strong>Inactive questionnaires</strong> do not collect new responses and do not allow invitations or reminders.
</p>

<p>
    <?= $community['name'] ?>'s community <?= $survey['type'] ?>s questionnaire is currently
    <strong><?= $currentlyActive ? 'active' : 'inactive' ?></strong>.
</p>

<?php
    echo $this->Form->create($survey);
    echo $this->Form->hidden(
        'active',
        ['value' =>  $currentlyActive ? 0 : 1]
    );
    echo $this->Form->button(
        ($currentlyActive ? 'Deactivate' : 'Activate') . ' Questionnaire',
        [
            'class' => 'btn ' . ($currentlyActive ? 'btn-danger' : 'btn-success')
        ]
    );
    echo $this->Form->end();
