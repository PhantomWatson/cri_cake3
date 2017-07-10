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
        <div class="btn-group" id="community-index-categories">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                Filter by status:
                <strong>Active</strong>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
                <?php foreach (['active', 'inactive', 'all'] as $category): ?>
                    <li>
                        <button data-category="<?= $category ?>" class="btn btn-link btn-block">
                            <?= ucwords($category) ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

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

    <?php $this->append('community-index-pagination'); ?>
        <nav aria-label="Communities index navigation" class="communities-index-pagination input-group">
            <div class="pagination input-group-btn">
                <button aria-label="Previous" class="btn btn-default">
                    <span aria-hidden="true">&laquo;</span>
                </button>

                <?php /* for ($n = 1; $n <= ceil(count($communities) / $perPage); $n++): ?>
                    <button class="btn btn-default" data-page-num="<?= $n ?>">
                        <?= $n ?>
                    </button>
                <?php endfor; */ ?>

                <button aria-label="Next" class="btn btn-default">
                    <span aria-hidden="true">&raquo;</span>
                </button>
            </div>
        </nav>
    <?php $this->end(); ?>
    <?= $this->fetch('community-index-pagination') ?>

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
            <?php foreach ($communities as $community): ?>
                <tr data-community-name="<?= $community['name'] ?>" data-active="<?= $community['active'] ? 1 : 0 ?>">
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

    <?= $this->fetch('community-index-pagination') ?>
</div>

<?= $this->element('DataCenter.jquery_ui') ?>
<?php $this->element('script', ['script' => 'admin/communities-index']); ?>

<?php $this->append('buffered'); ?>
    adminCommunitiesIndex.init({
        perPage: <?= $perPage ?>
    });
<?php $this->end();
