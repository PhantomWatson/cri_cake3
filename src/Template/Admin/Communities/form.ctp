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
            'class' => 'form-control',
            'div' => ['class' => 'form-group']
        ]
    );
    echo $this->Form->input(
        'area_id',
        [
            'class' => 'form-control',
            'div' => ['class' => 'form-group'],
            'empty' => true,
            'label' => 'Geographic Area'
        ]
    );
    echo $this->Form->input(
        'fast_track',
        [
            'class' => 'form-control',
            'div' => ['class' => 'form-group']
        ]
    );
    $scores = [0, 1, 2, 2.5, 3, 3.5, 4, 5];
    if ($this->request->action == 'admin_edit') {
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
            'class' => 'form-control',
            'div' => ['class' => 'form-group'],
            'escape' => false,
            'label' => [
                'text' => 'PWR<sup>3</sup> &trade; Score',
                'escape' => false
            ],
            'options' => array_combine($scores, $scores),
            'type' => 'select'
        ]
    );

    if (isset($this->request->data['Community']['town_meeting_date'])) {
        $selectedDateSplit = explode('-', $this->request->data['Community']['town_meeting_date']);
        $selectedYear = $selectedDateSplit[0];
        $minYear = min($selectedYear, date('Y'));
    } else {
        $minYear = date('Y');
    }
    $dateFields = $this->Form->input(
        'town_meeting_date',
        [
            'class' => 'form-control',
            'div' => [
                'class' => 'form-group form-inline',
                'id' => 'meeting_date_fields'
            ],
            'label' => false,
            'minYear' => $minYear,
            'maxYear' => date('Y') + 1
        ]
    );
?>
<div class="custom_radio">
    <?= $this->Form->input(
        'meeting_date_set',
        [
            'after' => $dateFields,
            'before' => '<span class="fake_label">Town meeting</span><br />',
            'default' => isset($this->request->data['Community']['town_meeting_date']),
            'legend' =>  false,
            'options' =>  [
                0 => 'Has not been scheduled yet',
                1 => 'Is scheduled for the following date'
            ],
            'separator' => '<br />',
            'type'      =>  'radio'
        ]
    ) ?>
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

<div class="panel panel-default" id="client_interface">
    <div class="panel-heading">
        <h2 class="panel-title">
            Client
        </h2>

        <a href="#" class="btn btn-default btn-xs toggle_add" data-user-type="client">
            Add new client
        </a>

        <?php if (! empty($clients)): ?>
            <a href="#" class="btn btn-default btn-xs toggle_select" data-user-type="client">
                Add existing client
            </a>
        <?php endif; ?>
    </div>

    <?php if (isset($clientErrors) && ! empty($clientErrors)): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">
                    &times;
                </span>
            </button>
            <?php if (count($clientErrors) > 1): ?>
                <ul>
                    <?php foreach ($clientErrors as $errMsg): ?>
                        <li>
                            <?= $errMsg ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <?= $clientErrors[0] ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="panel-body">
        <?php
            echo $this->Form->input(
                'client_id',
                [
                    'class' => 'form-control',
                    'data-user-type' => 'client',
                    'div' => [
                        'id' => 'client_select',
                        'class' => 'form-group well'
                    ],
                    'empty' => true,
                    'label' => 'Select client',
                    'options' => $clients
                ]
            );
        ?>
        <div id="client_add" class="well">
            <?php
                echo $this->Form->input(
                    'NewClientEntry.name',
                    [
                        'class' => 'form-control',
                        'div' => ['class' => 'form-group'],
                        'label' => 'New client name'
                    ]
                );
                echo $this->Form->input(
                    'NewClientEntry.title',
                    [
                        'class' => 'form-control',
                        'div' => ['class' => 'form-group'],
                        'label' => 'Job Title'
                    ]
                );
                echo $this->Form->input(
                    'NewClientEntry.organization',
                    [
                        'class' => 'form-control',
                        'div' => ['class' => 'form-group']
                    ]
                );
                echo $this->Form->input(
                    'NewClientEntry.email',
                    [
                        'class' => 'form-control',
                        'div' => ['class' => 'form-group'],
                        'type' => 'email'
                    ]
                );
                echo $this->Form->input(
                    'NewClientEntry.phone',
                    [
                        'class' => 'form-control',
                        'div' => ['class' => 'form-group']
                    ]
                );
                echo $this->Form->input(
                    'NewClientEntry.password',
                    [
                        'class' => 'form-control',
                        'div' => ['class' => 'form-group'],
                        'type' => 'text'
                    ]
                );
            ?>
            <button class="add btn btn-default" data-user-type="client">
                Add new client
            </button>
        </div>
    </div>
</div>

<div class="panel panel-default" id="consultant_interface">
    <div class="panel-heading">
        <h2 class="panel-title">
            Consultant
        </h2>

        <a href="#" class="btn btn-default btn-xs toggle_add" data-user-type="consultant">
            Add new consultant
        </a>

        <?php if (! empty($consultants)): ?>
            <a href="#" class="btn btn-default btn-xs toggle_select" data-user-type="consultant">
                Add existing consultant
            </a>
        <?php endif; ?>
    </div>

    <?php if (isset($consultantErrors) && ! empty($consultantErrors)): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">
                    &times;
                </span>
            </button>
            <?php if (count($consultantErrors) > 1): ?>
                <ul>
                    <?php foreach ($consultantErrors as $errMsg): ?>
                        <li>
                            <?= $errMsg ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <?= $consultantErrors[0] ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="panel-body">
        <?php
            echo $this->Form->input(
                'consultant_id',
                [
                    'class' => 'form-control',
                    'data-user-type' => 'consultant',
                    'div' => [
                        'id' => 'consultant_select',
                        'class' => 'form-group well'
                    ],
                    'empty' => true,
                    'label' => 'Select consultant',
                    'options' => $consultants
                ]
            );
        ?>
        <div id="consultant_add" class="well">
            <?php
                echo $this->Form->input(
                    'NewConsultantEntry.name',
                    [
                        'class' => 'form-control',
                        'div' => ['class' => 'form-group'],
                        'label' => 'New consultant name'
                    ]
                );
                echo $this->Form->input(
                    'NewConsultantEntry.title',
                    [
                        'class' => 'form-control',
                        'div' => ['class' => 'form-group'],
                        'label' => 'Job Title'
                    ]
                );
                echo $this->Form->input(
                    'NewConsultantEntry.organization',
                    [
                        'class' => 'form-control',
                        'div' => ['class' => 'form-group']
                    ]
                );
                echo $this->Form->input(
                    'NewConsultantEntry.email',
                    [
                        'class' => 'form-control',
                        'div' => ['class' => 'form-group'],
                        'type' => 'email'
                    ]
                );
                echo $this->Form->input(
                    'NewConsultantEntry.phone',
                    [
                        'class' => 'form-control',
                        'div' => ['class' => 'form-group']
                    ]
                );
                echo $this->Form->input(
                    'NewConsultantEntry.password',
                    [
                        'class' => 'form-control',
                        'div' => ['class' => 'form-group'],
                        'type' => 'text'
                    ]
                );
            ?>
            <button class="add btn btn-default" data-user-type="consultant">
                Add new consultant
            </button>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title">
            Surveys
        </h2>
    </div>
    <div class="panel-body">
        <?= $this->element(
            'Communities'.DS.'link_survey',
            ['type' => 'Official']
        ) ?>

        <hr />

        <?= $this->element(
            'Communities'.DS.'link_survey',
            ['type' => 'Organization']
        ) ?>
    </div>
</div>

<?php
    $label = $this->request->action == 'admin_add'
        ? 'Add Community'
        : 'Update Community';
    echo $this->Form->button(
        $label,
        ['class' => 'btn btn-primary']
    );
    echo $this->Form->end();

    $this->Html->script('admin', ['block' => 'scriptBottom']);
?>

<?php $this->append('buffered'); ?>
    communityForm.init({
        community_id: <?= isset($communityId) ? $communityId : 'null' ?>,
        selected_clients: <?= json_encode($selectedClients) ?>,
        selected_consultants: <?= json_encode($selectedConsultants) ?>
    });");
<?php $this->end();