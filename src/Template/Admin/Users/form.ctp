<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-arrow-left"></span> Back to Users',
        [
            'prefix' => 'admin',
            'controller' => 'Users',
            'action' => 'index'
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
</p>

<?php
    echo $this->Form->create($user);
    echo $this->Form->input(
        'name',
        ['class' => 'form-control']
    );
    echo $this->Form->input(
        'title',
        [
            'class' => 'form-control',
            'label' => 'Job Title'
        ]
    );
    echo $this->Form->input(
        'organization',
        ['class' => 'form-control']
    );
    echo $this->Form->input(
        'email',
        ['class' => 'form-control']
    );
    echo $this->Form->input(
        'phone',
        ['class' => 'form-control']
    );

    if ($this->request->action == 'add' && $this->request->prefix == 'admin') {
        echo $this->Form->input(
            'new_password',
            [
                'autocomplete' => 'off',
                'class' => 'form-control',
                'type' => 'password'
            ]
        );
        echo $this->Form->input(
            'confirm_password',
            [
                'class' => 'form-control',
                'label' => 'Confirm password',
                'type' => 'password'
            ]
        );
    } elseif ($this->request->action == 'edit' && $this->request->prefix == 'admin') {
        echo $this->Form->input(
            'new_password',
            [
                'autocomplete' => 'off',
                'class' => 'form-control',
                'label' => 'Change password',
                'required' => false,
                'type' => 'password'
            ]
        );
        echo $this->Form->input(
            'confirm_password',
            [
                'class' => 'form-control',
                'label' => 'Repeat new password',
                'type' => 'password'
            ]
        );
    }

    echo $this->Form->input(
        'role',
        [
            'after' => '<span class="note">Admins automatically have access to all communities and site functions</span>',
            'class' => 'form-control',
            'options' => $roles
        ]
    );
?>

<div id="consultant_communities">
    <?php
        echo $this->Form->input(
            'all_communities',
            [
                'label' =>  false,
                'options' =>  [
                    1 => 'All communities',
                    0 => 'Only specific communities'
                ],
                'templates' => [
                    'inputContainer' => '<div class="form-group all_communities {{type}}{{required}}"><span class="fake_label">Which communities should this consultant have access to?</span><br />{{content}}</div>',
                    'inputContainerError' => '<div class="form-group all_communities {{type}}{{required}} error"><span class="fake_label">Which communities should this consultant have access to?</span><br />{{content}}{{error}}</div>',
                    'radio' => '<input type="radio" name="{{name}}" value="{{value}}"{{attrs}}>',
                    'radioWrapper' => '{{label}}<br />'
                ],
                'type' => 'radio'
            ]
        );
        echo $this->Form->input(
            'community',
            [
                'class' => 'form-control',
                'empty' => 'Choose one or more communities to allow this user access to...',
                'label' => false,
                'options' => $communities
            ]
        );
    ?>
</div>

<div id="client_communities">
    <?= $this->Form->input(
        'client_communities.0.id',
        [
            'class' => 'form-control',
            'empty' => 'Choose a community to assign this client to...',
            'label' => false,
            'multiple' => false,
            'options' => $communities,
            'required' => false,
            'type' => 'select'
        ]
    ) ?>
</div>

<?php
    $label = ($this->request->action == 'add') ? 'Add User' : 'Update';
    echo $this->Form->button(
        $label,
        ['class' => 'btn btn-primary']
    );
    echo $this->Form->end();
    $this->Html->script('admin', ['block' => 'scriptBottom']);
?>
<?php $this->append('buffered'); ?>
    adminUserEdit.init({
        selected_communities: <?= json_encode($selectedCommunities) ?>
    });
<?php $this->end(); ?>