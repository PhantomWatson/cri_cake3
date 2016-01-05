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
            $community->id
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
            $community->id
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
            <p>
                Survey URL:
                <span class="survey_url">
                    <?php if ($survey['sm_url']): ?>
                        <a href="<?= $survey['sm_url'] ?>">
                            <?= $survey['sm_url'] ?>
                        </a>
                    <?php else: ?>
                        unknown
                    <?php endif; ?>
                </span>
            </p>

            <?= $this->Html->link(
                'Update link',
                [
                    'action' => 'link',
                    $community->id,
                    str_replace('_survey', '', $survey['type'])
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
    $this->element('script', ['script' => 'admin']);
?>
<?php $this->append('buffered'); ?>
    surveyOverview.init({
        community_id: <?= $community->id ?>
    });
<?php $this->end(); ?>