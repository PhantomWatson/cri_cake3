<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-arrow-left"></span> Back to Communities',
        [
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'index'
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-pencil"></span> Edit Community',
        [
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'edit',
            'id' => $communityId
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-tasks"></span> Community Progress',
        [
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'progress',
            $communityId
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
</p>

<?php
    echo $this->element('Surveys'.DS.'overview');
    $this->Html->script('client', ['block' => 'scriptBottom']);
?>
<?php $this->append('buffered'); ?>
    surveyOverview.init();
<?php $this->end(); ?>