<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?php if ($survey['sm_url']): ?>
        <?= $this->Html->link(
            '<span class="glyphicon glyphicon-arrow-left"></span> Back to Survey Overview',
            [
                'prefix' => 'admin',
                'controller' => 'Surveys',
                'action' => 'view',
                $community->id,
                $survey->type
            ],
            [
                'class' => 'btn btn-default',
                'escape' => false
            ]
        ) ?>
    <?php else: ?>
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
    <?php endif; ?>
</p>

<p>
    After a CRI survey is created in <a href="http://surveymonkey.com/">SurveyMonkey</a>, you must create a link between the
    community and the new survey in order to enable survey invitations and response analysis.
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
                Status:
                <span class="link_status">
                    <?php if ($survey['sm_url']): ?>
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Linked
                    <?php else: ?>
                        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Not linked
                    <?php endif; ?>
                </span>
            </p>

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

            <p class="loading_messages">
            </p>

            <?= $this->Form->create($survey) ?>

            <ul class="actions">
                <li>
                    <button class="lookup btn btn-default">
                        Select Survey
                    </button>
                </li>
                <li>
                    <button class="show_details btn btn-default">
                        Show Details
                    </button>
                </li>
                <li>
                    <?= $this->Form->button(
                        $survey->isNew() ? 'Link Survey' : 'Update Link',
                        ['class' => 'btn btn-primary']
                    ) ?>
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
                                'label' => 'SurveyMonkey Survey ID',
                                'type' => 'number'
                            ]
                        );
                        echo $this->Form->input(
                            'sm_url',
                            [
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
            </div>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

<?php $this->element('script', ['script' => 'admin']); ?>
<?php $this->append('buffered'); ?>
    surveyLink.init({
        community_id: <?= $community->id ?>,
        survey_type: '<?= $survey->type ?>'
    });
<?php $this->end(); ?>