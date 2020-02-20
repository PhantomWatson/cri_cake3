<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $communities
 * @var mixed $hasPasswordError
 * @var mixed $roles
 * @var mixed $salutations
 * @var mixed $selectedCommunities
 * @var string $titleForLayout
 * @var mixed $user
 */
?>
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
    echo $this->Form->create(
        $user,
        ['id' => 'UserForm']
    );
    echo $this->Form->control(
        'salutation',
        [
            'class' => 'form-control',
            'options' => $salutations
        ]
    );
    echo $this->Form->control(
        'name',
        ['class' => 'form-control']
    );
    echo $this->Form->control(
        'title',
        [
            'class' => 'form-control',
            'label' => 'Job Title'
        ]
    );
    echo $this->Form->control(
        'organization',
        ['class' => 'form-control']
    );
    echo $this->Form->control(
        'email',
        ['class' => 'form-control']
    );
    echo $this->Form->control(
        'phone',
        ['class' => 'form-control']
    );
?>

<?= $this->Form->control(
    'role',
    [
        'after' => '<span class="note">Admins automatically have access to all communities and site functions</span>',
        'class' => 'form-control',
        'options' => $roles
    ]
) ?>

<div id="consultant_communities">
    <?php
        echo $this->Form->control(
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
        echo $this->Form->control(
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
    <?= $this->Form->control(
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
    $passwordFields = $this->Form->control(
        'new_password',
        [
            'autocomplete' => 'off',
            'class' => 'form-control',
            'label' => $this->request->getParam('action') == 'add' ? 'Password' : 'New Password',
            'type' => 'password',
            'value' => ''
        ]
    );
    $passwordFields .= $this->Form->control(
        'confirm_password',
        [
            'class' => 'form-control',
            'label' => 'Confirm password',
            'type' => 'password',
            'value' => ''
        ]
    );
?>

<?php if ($this->request->getParam('prefix') == 'admin' && $this->request->getParam('action') == 'edit'): ?>
    <?php if ($hasPasswordError): ?>
        <?= $passwordFields ?>
    <?php else: ?>
        <div id="password-fields-button" class="form-group">
            <button class="btn btn-default">
                Change password
            </button>
        </div>
        <div id="password-fields" style="display: none;">
            <?= $passwordFields ?>
        </div>
    <?php endif; ?>
<?php elseif ($this->request->getParam('prefix') == 'admin' && $this->request->getParam('action') == 'add'): ?>
    <?= $passwordFields ?>
<?php endif; ?>

<?php
    $label = ($this->request->getParam('action') == 'add') ? 'Add User' : 'Update';
    echo $this->Form->button(
        $label,
        ['class' => 'btn btn-primary']
    );
    echo $this->Form->end();
    $this->element('script', ['script' => 'form-protector']);
    $this->element('script', ['script' => 'admin/user-form']);
?>

<?php $this->append('buffered'); ?>
    adminUserEdit.init({
        selected_communities: <?= json_encode($selectedCommunities) ?>
    });
    formProtector.protect('UserForm', {});
<?php $this->end(); ?>
