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
            $communityId
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

<div class="survey_overview">
    <?php
        $tableTemplate = [
            'formGroup' => '{{label}}</td><td>{{input}}',
            'inputContainer' => '<tr><td class="form-group {{type}}{{required}}">{{content}}</td></tr>',
            'inputContainerError' => '<tr><td class="form-group {{type}}{{required}}">{{content}}{{error}}</td></tr>'
        ] + require(ROOT.DS.'config'.DS.'bootstrap_form.php');
        $this->Form->templates($tableTemplate);
    ?>

    <div class="panel panel-default link_survey">
        <div class="panel-heading">
            <h3 class="panel-title">
                Link
            </h3>
        </div>
        <div class="panel-body">

            <div class="link_status">
                <?php if ($surveyUrl): ?>
                    <p>
                        Survey URL:
                        <a href="<?= $surveyUrl ?>">
                            <?= $surveyUrl ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>

            <?= $this->Html->link(
                'Update link',
                [
                    'action' => 'link',
                    $community->id,
                    str_replace('_survey', '', $surveyType)
                ],
                [
                    'class' => 'btn btn-default'
                ]
            ) ?>
        </div>
    </div>
</div>


<?php
    if ($survey['id']) {
        echo $this->element('Surveys'.DS.'overview');
    }
    $this->Html->script('admin', ['block' => 'scriptBottom']);
?>
<?php $this->append('buffered'); ?>
    surveyOverview.init({
        community_id: <?= $communityId ?>
    });
<?php $this->end(); ?>