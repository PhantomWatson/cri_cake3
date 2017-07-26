<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php
    echo $this->Form->create(false);
    echo $this->Form->input(
        'community_id',
        [
            'class' => 'form-control',
            'div' => ['class' => 'form-group'],
        ]
    );
    if ($redirect) {
        echo $this->Form->input(
            'redirect',
            [
                'type' => 'hidden',
                'value' => $redirect
            ]
        );
    }
    echo $this->Form->button(
        'Continue',
        ['class' => 'btn btn-primary']
    );
    echo $this->Form->end();
