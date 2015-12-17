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
                <span class="link_label">
                    <?php if ($survey['sm_url']): ?>
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    <?php else: ?>
                        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                    <?php endif; ?>
                </span>
            </h3>
        </div>
        <div class="panel-body">

            <div class="link_status">
                <?php if ($survey['sm_url']): ?>
                    <p>
                        Survey URL:
                        <a href="<?= $survey['sm_url'] ?>">
                            <?= $survey['sm_url'] ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>

            <?= $this->Form->create($survey) ?>

            <ul class="actions">
                <li>
                    <a href="#" class="lookup btn btn-default">
                        Select survey
                    </a>
                </li>
                <li>
                    <a href="#" class="show_details btn btn-default">
                        Show details
                    </a>
                </li>
            </ul>

            <div class="lookup_results well"></div>

            <div class="details well">
                <?php
                    echo $this->Form->hidden("id");
                    echo $this->Form->hidden("type");
                    echo $this->Form->hidden("community_id");
                ?>

                <table class="table">
                    <?php
                        $this->Form->templates($tableTemplate);
                        echo $this->Form->input(
                            'sm_id',
                            [
                                'class' => 'form-control survey_sm_id',
                                'label' => 'SurveyMonkey Survey ID',
                                'type' => 'number'
                            ]
                        );
                        echo $this->Form->input(
                            'sm_url',
                            [
                                'class' => 'form-control survey_url',
                                'label' => 'SurveyMonkey Survey URL'
                            ]
                        );
                        use Cake\Utility\Inflector;
                        foreach ($qnaIdFields as $qnaIdField) {
                            $label = Inflector::humanize($qnaIdField);
                            $label = str_ireplace('qid', 'Question ID', $label);
                            $label = str_ireplace('aid', 'Answer ID', $label);
                            $label = str_ireplace('pwrrr', 'PWR<sup>3</sup>&trade;', $label);
                            echo $this->Form->input(
                                $qnaIdField,
                                [
                                    'class' => 'form-control',
                                    'data-fieldname' => $qnaIdField,
                                    'label' => [
                                        'escape' => false,
                                        'text' => $label
                                    ],
                                    'type' => 'number'
                                ]
                            );
                        }

                        // Return to default Bootstrap form template
                        $this->Form->templates('bootstrap_form');
                    ?>
                </table>
                <?= $this->Form->button(
                    'Update Survey Details',
                    ['class' => 'btn btn-primary']
                ) ?>
                <?= $this->Form->end() ?>
            </div>
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