<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-arrow-left"></span> Back to Clients',
        [
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'clients',
            $communityId
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-list"></span> Add an Existing Client',
        [
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'selectClient',
            $communityId
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
</p>

<div id="<?= $role ?>_add">
    <?php
        $tableTemplate = [
            'formGroup' => '{{label}}</td><td>{{input}}',
            'inputContainer' => '<tr><td class="form-group {{type}}{{required}}">{{content}}</td></tr>',
            'inputContainerError' => '<tr><td class="form-group {{type}}{{required}}">{{content}}{{error}}</td></tr>'
        ] + require(ROOT.DS.'config'.DS.'bootstrap_form.php');
        $this->Form->templates($tableTemplate);
        echo $this->Form->create(
            $client,
            ['id' => 'ClientForm']
        );
    ?>
    <table class="table">
        <?php
            echo $this->Form->input(
                'salutation',
                [
                    'class' => 'form-control',
                    'div' => ['class' => 'form-group'],
                    'options' => $salutations
                ]
            );
            echo $this->Form->input(
                'name',
                [
                    'class' => 'form-control',
                    'div' => ['class' => 'form-group'],
                    'label' => 'Name'
                ]
            );
            echo $this->Form->input(
                'title',
                [
                    'class' => 'form-control',
                    'div' => ['class' => 'form-group'],
                    'label' => 'Job Title'
                ]
            );
            echo $this->Form->input(
                'organization',
                [
                    'class' => 'form-control',
                    'div' => ['class' => 'form-group']
                ]
            );
            echo $this->Form->input(
                'email',
                [
                    'class' => 'form-control',
                    'div' => ['class' => 'form-group'],
                    'type' => 'email'
                ]
            );
            echo $this->Form->input(
                'phone',
                [
                    'class' => 'form-control',
                    'div' => ['class' => 'form-group']
                ]
            );
            echo $this->Form->input(
                'unhashed_password',
                [
                    'class' => 'form-control',
                    'div' => ['class' => 'form-group'],
                    'label' => 'Password',
                    'type' => 'text',
                    'value' => null
                ]
            );
        ?>
    </table>

    <?= $this->Form->button(
        'Add new client for '.$communityName,
        ['class' => 'btn btn-primary']
    ) ?>

    <?= $this->Form->end() ?>
</div>

<?php $this->element('script', ['script' => 'form-protector']); ?>
<?php $this->append('buffered'); ?>
    formProtector.protect('ClientForm');
<?php $this->end();
