<div id="surveys_admin_index">
    <div class="page-header">
        <h1>
            <?php echo $titleForLayout; ?>
        </h1>
    </div>

    <a href="#" class="help_toggler">
        Is the community you're looking for not listed?
    </a>
    <p class="help_message">
        The following are all of the communities
        <strong>currently associated with a client</strong>.
        If a community has been added to the site but does not appear in this list,
        you may need to visit the
        <?= $this->Html->link(
            'communities admin page',
            [
                'prefix' => 'admin',
                'controller' => 'Communities',
                'action' => 'index'
            ]
        ) ?>,
        edit the community, and select the appropriate client.
    </p>

    <table class="table">
        <thead>
            <tr>
                <th>
                    <?= $this->Paginator->sort('name', 'Community') ?>
                </th>
                <th>
                    <?= $this->Paginator->sort('score', 'Score') ?>
                </th>
                <th>
                    Official Survey
                </th>
                <th>
                    Organization Survey
                </th>
                <th class="actions">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($communities as $community): ?>
                <tr>
                    <td>
                        <?= $community->name ?>
                    </td>
                    <td>
                        <?= $community->score ?>
                    </td>
                    <td>
                        <?php if (empty($community->official_survey->id)): ?>
                            <?= $this->Html->link(
                                'Add',
                                [
                                    'prefix' => 'admin',
                                    'controller' => 'Communities',
                                    'action' => 'edit',
                                    $community->id
                                ],
                                [
                                    'class' => 'btn btn-success',
                                    'escape' => false
                                ]
                            ) ?>
                        <?php else: ?>
                            <?= $this->Html->link(
                                'View',
                                [
                                    'prefix' => 'admin',
                                    'controller' => 'surveys',
                                    'action' => 'view',
                                    $community->official_survey->id
                                ],
                                [
                                    'class' => 'btn btn-default',
                                    'escape' => false
                                ]
                            ) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (empty($community->organization_survey->id)): ?>
                            <?= $this->Html->link(
                                'Add',
                                [
                                    'prefix' => 'admin',
                                    'controller' => 'Communities',
                                    'action' => 'edit',
                                    $community->id
                                ],
                                [
                                    'class' => 'btn btn-success',
                                    'escape' => false
                                ]
                            ) ?>
                        <?php else: ?>
                            <?= $this->Html->link(
                                'View',
                                [
                                    'prefix' => 'admin',
                                    'controller' => 'surveys',
                                    'action' => 'view',
                                    $community->organization_survey->id
                                ],
                                [
                                    'class' => 'btn btn-default',
                                    'escape' => false
                                ]
                            ) ?>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <div class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                Actions <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <?= $this->Html->link(
                                        'Edit Community',
                                        [
                                            'prefix' => 'admin',
                                            'controller' => 'Communities',
                                            'action' => 'edit',
                                            $community->id
                                        ]
                                    ) ?>
                                </li>
                                <li>
                                    <?= $this->Html->link(
                                        'View Performance Charts',
                                        [
                                            'prefix' => false,
                                            'controller' => 'Communities',
                                            'action' => 'view',
                                            $community->id
                                        ]
                                    ) ?>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $this->Html->script('admin', ['block' => 'scriptBottom']); ?>

<?php $this->append('buffered'); ?>
    adminSurveysIndex.init();
<?php $this->end(); ?>