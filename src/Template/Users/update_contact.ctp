<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php
    echo $this->Form->create($user);
    echo $this->Form->input(
        'name',
        [
            'class' => 'form-control',
            'div' => ['class' => 'form-group']
        ]
    );
    echo $this->Form->input(
        'email',
        [
            'class' => 'form-control',
            'div' => ['class' => 'form-group']
        ]
    );
    echo $this->Form->button(
        'Update',
        ['class' => 'btn btn-primary']
    );
    echo $this->Form->end();
?>