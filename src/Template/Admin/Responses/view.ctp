<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-arrow-left"></span> Back to Survey Overview',
        [
            'prefix' => 'admin',
            'controller' => 'Surveys',
            'action' => 'view',
            $communityId,
            $surveyType
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
    <?php if (! empty($responses)): ?>
        <a href="#" class="btn btn-default" id="show_respondents">
            Show respondent info
        </a>
    <?php endif; ?>
</p>

<div id="admin_responses_view">
    <p>
        These are the currently known responses to
        <strong>
            <?= $communityName ?>'s
            community
            <?= $surveyType == 'official' ? 'leadership' : 'organization' ?>
        </strong>
        survey. Incomplete responses are excluded, and recent responses may have not been imported yet.
    </p>

    <section>
        <h2>
            Responses
        </h2>
        <?php if (empty($responses)): ?>
            <p class="alert alert-info">
                No responses have been imported yet.
            </p>
        <?php else: ?>
            <?= $this->element('Respondents'.DS.'admin_table') ?>
        <?php endif; ?>
    </section>

    <section>
        <h2>
            Alignment
        </h2>
        <?php if ($internalAlignment): ?>
            <p class="internal_alignment">
                Internal Alignment:
                <strong>
                    <?= round($internalAlignment, 2) ?>
                </strong>
            </p>
        <?php endif; ?>
        <?= $this->Form->create($survey) ?>
        <?php
            if ($alignmentLastSet) {
                $alignmentLastSetMsg = '<br />Last modified: '.$alignmentLastSet;
            } else {
                $alignmentLastSetMsg = '';
            }
            echo $this->Form->input(
                'alignment',
                [
                    'class' => 'form-control',
                    'div' => [
                        'class' => 'form-group'
                    ],
                    'label' => [
                        'text' => 'Administrator-determined alignment (percent)'.$alignmentLastSetMsg,
                        'escape' => false
                    ],
                    'max' => 100,
                    'min' => 0,
                    'type' => 'number'
                ]
            );
        ?>
        <?= $this->Form->input(
            'alignment_passed',
            [
                'class' => 'form-control',
                'div' => [
                    'class' => 'form-group'
                ],
                'label' => 'Has this community passed its leadership alignment assessment?',
                'options' => [
                    0 => 'Not determined',
                    -1 => 'Failed',
                    1 => 'Passed'
                ],
                'type' => 'select'
            ]
        ) ?>
        <?= $this->Form->button(
            'Update',
            ['class' => 'btn btn-primary']
        ) ?>
        <?= $this->Form->end() ?>
    </section>
</div>