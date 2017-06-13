<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-plus"></span> Add a New Client',
        [
            'prefix' => 'admin',
            'controller' => 'Communities',
            'action' => 'addClient',
            $community['id']
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
            $community['id']
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
</p>

<?php if (empty($community['surveys'])): ?>
    <p class="alert alert-danger">
        This community does not have its
        <?= $this->Html->link(
            'community officials questionnaire linked',
            [
                'prefix' => 'admin',
                'controller' => 'Surveys',
                'action' => 'link',
                $community->slug,
                'official'
            ]
        ) ?>
        yet. It is recommended that clients
        <strong>not be added</strong> until this is done.
    </p>
<?php endif; ?>

<?php if (empty($community['clients'])): ?>
    <p class="alert alert-info">
        This community does not have any client accounts associated with it.
    </p>
<?php else: ?>
    <table class="table" id="associated_clients">
        <thead>
            <tr>
                <th>
                    Name
                </th>
                <th>
                    Contact
                </th>
                <th>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($community['clients'] as $client): ?>
                <tr>
                    <td>
                        <?= $client['salutation'] ?>
                        <?= $client['name'] ?>

                        <span class="title">
                            <?php if ($client['title']): ?>
                                <br />
                                <?= $client['title'] ?>
                            <?php endif; ?>
                            <?php if ($client['organization']): ?>
                                <br />
                                <?= $client['organization'] ?>
                            <?php endif; ?>
                        </span>
                    </td>
                    <td>
                        <a href="mailto:<?= $client['email'] ?>">
                            <?= $client['email'] ?>
                        </a>

                        <?php if ($client['phone']): ?>
                            <br />
                            <?= $client['phone'] ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $this->Html->link(
                            'Edit',
                            [
                                'prefix' => 'admin',
                                'controller' => 'Users',
                                'action' => 'edit',
                                $client['id']
                            ],
                            ['class' => 'btn btn-default']
                        ) ?>
                        <?= $this->Html->link(
                            'Remove',
                            [
                                'prefix' => 'admin',
                                'controller' => 'Communities',
                                'action' => 'removeClient',
                                $client['id'],
                                $community['id']
                            ],
                            [
                                'class' => 'btn btn-default',
                                'confirm' => "Are you sure you want to remove {$client['name']} from this community?"
                            ]
                        ) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
