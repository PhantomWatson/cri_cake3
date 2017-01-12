<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<table class="table" id="alignmentCalcSettings">
    <thead>
        <tr>
            <th>
                Community
            </th>
            <th>
                Adjustment
            </th>
            <th>
                Threshhold
            </th>
            <th>
                Actions
            </th>
        </tr>
    </thead>
    <tbody>
        <tr class="default">
            <td>
                Default values for new communities
            </td>
            <td>
                <?= $settings['intAlignmentAdjustment'] ?>
            </td>
            <td>
                +/- <?= $settings['intAlignmentThreshhold'] ?>
            </td>
            <td>
                <?= $this->Html->link(
                    'Edit',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Settings',
                        'action' => 'editCalculationSettings'
                    ],
                    ['class' => 'btn btn-default']
                ) ?>
            </td>
        </tr>
        <?php foreach ($communities as $community): ?>
            <tr>
                <td>
                    <?= $community->name ?>
                </td>
                <td>
                    <?= $community->intAlignmentAdjustment ?>
                </td>
                <td>
                    +/- <?= $community->intAlignmentThreshhold ?>
                </td>
                <td>
                    <?= $this->Html->link(
                        'Edit',
                        [
                            'prefix' => 'admin',
                            'controller' => 'Communities',
                            'action' => 'edit',
                            $community->id
                        ],
                        ['class' => 'btn btn-default']
                    ) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p>
    <?php if ($this->request->query('show-dummy')): ?>
        <?= $this->Html->link(
            'Hide dummy communities',
            ['?' => ['show-dummy' => 0]]
        ) ?>
    <?php else: ?>
        <?= $this->Html->link(
            'Show dummy communities',
            ['?' => ['show-dummy' => 1]]
        ) ?>
    <?php endif; ?>
</p>
