<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p class="alert alert-info">
    These will be the default values applied to new communities when they are created, but can be changed in individual communities at any time.
</p>

<?= $this->Form->create() ?>

<?php foreach ($settings as $setting): ?>
    <?= $this->Form->input(
        'settings.'.$setting->id,
        [
            'label' => $setting->name,
            'max' => '99.99',
            'min' => '0',
            'step' => '0.01',
            'type' => 'number'
        ]
    ) ?>
<?php endforeach; ?>

<?= $this->Form->button(
    'Update',
    ['class' => 'btn btn-primary']
) ?>

<?= $this->Form->end(); ?>
