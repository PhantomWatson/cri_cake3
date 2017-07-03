<?php
    $Report = new \App\Reports\Reports();
?>
<div id="communities_admin_index">
    <div class="page-header">
        <h1>
            <?= $titleForLayout ?>
        </h1>
    </div>

    <p>
        <?php foreach ($buttons as $groupLabel => $buttonGroup): ?>
            <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <?php
                        echo "Filter by $groupLabel";
                        if (isset($filters['status'])) {
                            echo ': <strong>' . ucwords($filters['status']) . '</strong>';
                        }
                    ?>
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <?php foreach ($buttonGroup as $label => $filters): ?>
                        <li>
                            <?= $this->Html->link($label, compact('filters')) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <button class="btn btn-default" id="search_toggler">
            <span class="glyphicon glyphicon-search"></span>
            Search on this page
        </button>

        <?= $this->Html->link(
            'Add Community',
            [
                'prefix' => 'admin',
                'action' => 'add'
            ],
            ['class' => 'btn btn-success']
        ) ?>
    </p>

    <div style="display: none;" class="input-group" id="admin_community_search_form">
        <div class="input-group-addon">
            <span class="glyphicon glyphicon-search"></span>
        </div>
        <input type="text" name="search" class="form-control" placeholder="Enter community name" />
    </div>

    <?php if ($this->request->getQuery('search')): ?>
        <p class="alert alert-info" id="search_term">
            Search term: <strong><?= $this->request->getQuery('search') ?></strong>
            <?= $this->Html->link(
                'clear search',
                [
                    'prefix' => 'admin',
                    'controller' => 'Communities',
                    'action' => 'index',
                    '?' => []
                ]
            ) ?>
        </p>
    <?php endif; ?>

    <?= $this->element('pagination') ?>

    <table class="table communities">
        <thead>
            <tr>
                <th>
                    Community
                </th>
                <th>
                    Stage
                </th>
                <th>
                    Officials Questionnaire
                </th>
                <th>
                    Organizations Questionnaire
                </th>
                <th class="actions">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($communities)): ?>
                <tr>
                    <td colspan="4" class="no_results">
                        No communities found matching the specified parameters
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($communities as $community): ?>
                <tr data-community-name="<?= $community['name'] ?>">
                    <td>
                        <?= $community['name'] ?>
                        <br />
                        <span class="area_name">
                            <?= $community['parent_area']['name'] ?>
                        </span>
                    </td>
                    <td>
                        <?= str_replace('.0', '', $community['score']) ?>
                    </td>

                    <?php foreach (['official_survey', 'organization_survey'] as $surveyType): ?>
                        <td>
                            <?= $this->element('Surveys/dropdown', compact(
                                'community',
                                'surveyType'
                            )) ?>
                        </td>
                    <?php endforeach; ?>

                    <td class="actions">
                        <?= $this->element('Communities/dropdown', compact('community')) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?= $this->element('pagination') ?>
</div>

<?= $this->element('DataCenter.jquery_ui') ?>
<?php $this->element('script', ['script' => 'admin/communities-index']); ?>

<?php $this->append('buffered'); ?>
    adminCommunitiesIndex.init();
<?php $this->end();
