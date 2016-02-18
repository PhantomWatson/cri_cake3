<?php
    $tableTemplate = [
        'formGroup' => '<tr><td class="form-group {{type}}{{required}}">{{label}}</td><td>{{input}}</td></tr>',
        'inputContainer' => '{{content}}',
        'inputContainerError' => '{{content}}{{error}}'
    ] + require(ROOT.DS.'config'.DS.'bootstrap_form.php');
?>
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
</p>

<?php
    echo $this->Form->create(
        $community,
        ['id' => 'CommunityAdminEditForm']
    );
    echo $this->Form->input(
        'name',
        [
            'class' => 'form-control'
        ]
    );
    echo $this->Form->input(
        'local_area_id',
        [
            'empty' => true,
            'label' => 'Local Area (e.g. city)',
            'options' => $areas
        ]
    );
    echo $this->Form->input(
        'parent_area_id',
        [
            'empty' => true,
            'label' => 'Wider Area (e.g. county)',
            'options' => $areas
        ]
    );
    echo $this->Form->input(
        'fast_track',
        [
        ]
    );
    $scores = [1, 2, 2.5, 3, 3.5, 4, 5];
    if ($this->request->prefix == 'admin' && isset($communityId)) {
        $note = '<strong>Note:</strong> You\'re encouraged to edit this community\'s score through its ';
        $note .= $this->Html->link(
            'progress page',
            [
                'prefix' => 'admin',
                'controller' => 'Communities',
                'action' => 'progress',
                $communityId
            ]
        );
        $note .= ', which provides detailed information to help advise you.';
    } else {
        $note = '';
    }
    echo $this->Form->input(
        'score',
        [
            'after' => $note,
            'escape' => false,
            'label' => [
                'text' => 'Stage / PWR<sup>3</sup> &trade; Score',
                'escape' => false
            ],
            'options' => array_combine($scores, $scores),
            'type' => 'select'
        ]
    );
?>
<div class="custom_radio">
    <?= $this->Form->input(
        'meeting_date_set',
        [
            'default' => isset($community['town_meeting_date']),
            'legend' => false,
            'options' => [
                0 => 'Has not been scheduled yet',
                1 => 'Is scheduled for the following date'
            ],
            'type' =>  'radio'
        ]
    ) ?>

    <div id="meeting_date_fields">
        <?php
            if (isset($community['town_meeting_date'])) {
                $selectedDateSplit = explode('-', $community['town_meeting_date']);
                $selectedYear = $selectedDateSplit[0];
                $minYear = min($selectedYear, date('Y'));
            } else {
                $minYear = date('Y');
            }
            $template = [
                'dateWidget' => '{{month}}{{day}}{{year}}',
                'inputContainer' => '<div class="form-group form-inline {{type}}{{required}}">{{content}}</div>',
                'inputContainerError' => '<div class="form-group {{type}}{{required}} error">{{content}}{{error}}</div>',
                'select' => '<select name="{{name}}" class="form-control"{{attrs}}>{{content}}</select>'
            ];
            $this->Form->templates($template);
            echo $this->Form->input(
                'town_meeting_date',
                [
                    'label' => false,
                    'minYear' => $minYear,
                    'maxYear' => date('Y') + 1
                ]
            );
            $this->Form->templates('bootstrap_form');
        ?>
    </div>

    <?= $this->Form->input(
        'public',
        [
            'before' => '<span class="fake_label">Who should be able to see this community\'s performance report?</span><br />',
            'escape' => false,
            'legend' =>  false,
            'options' =>  [
                1 => '<strong>Public:</strong> Everyone',
                0 => '<strong>Private:</strong> Only the client, admins, and appropriate consultants'
            ],
            'separator' => '<br />',
            'type'      =>  'radio'
        ]
    ) ?>
</div>

<?php
    $label = $this->request->action == 'add'
        ? 'Add Community'
        : 'Update Community';
    echo $this->Form->button(
        $label,
        ['class' => 'btn btn-primary']
    );
    echo $this->Form->end();

    $this->element('script', ['script' => 'admin']);
    $this->element('script', ['script' => 'form-protector']);
?>

<?php $this->append('buffered'); ?>
    communityForm.init({
        community_id: <?= isset($communityId) ? $communityId : 'null' ?>,
        areaTypes: <?= json_encode($areaTypes) ?>
    });
    formProtector.protect('CommunityAdminEditForm');
<?php $this->end();