<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<section class="report">
    <h2>
        Download Reports

        <?php $icon = '<img src="/data_center/img/icons/document-excel-table.png" alt="Microsoft Excel (.xlsx)" />'; ?>
        <?= $this->Html->link(
            $icon . ' Admin Report',
            ['action' => 'admin'],
            [
                'class' => 'btn btn-sm btn-default',
                'escape' => false,
                'title' => 'Download the version of this report for CRI administrators as a Microsoft Excel (.xlsx) file'
            ]
        ) ?>
        <?= $this->Html->link(
            $icon . ' OCRA Report',
            ['action' => 'ocra'],
            [
                'class' => 'btn btn-sm btn-default',
                'escape' => false,
                'title' => 'Download an OCRA version of this report as a Microsoft Excel (.xlsx) file'
            ]
        ) ?>
    </h2>

    <p>
        The OCRA Report excludes PWR<sup>3</sup> and internal alignment calculations, but is otherwise the same as
        the admin version of the report. Communities in bold have had activity in the last 30 days.
    </p>
</section>

<section class="report">
    <h2>
        Community Officials Questionnaire (Step Two)
    </h2>

    <p>
        Click the links in the table headers below to expand and see more details, and click on the notes icon
        (<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>) to view notes related to a community.
        Click on the activities icon (<span class="glyphicon glyphicon-list" aria-hidden="true"></span>) to view
        details about recent activities.
    </p>

    <table class="table report">
        <thead>
            <tr class="col-group-headers">
                <td>
                    <button class="survey-toggler">
                        Minimize
                    </button>
                </td>
                <td class="spacer"></td>
                <td colspan="3"></td>
                <th colspan="2">
                    PWR<sup>3</sup> Alignment
                </th>
                <th colspan="6">
                    <button>
                        Internal Alignment
                    </button>
                </th>
                <th colspan="2">
                    Aware of Plan
                </th>
                <td colspan="3"></td>
            </tr>
            <tr class="general-header">
                <th>
                    Community
                </th>
                <?= $this->Reports->surveyHeader($sectors, 'officials'); ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report as $communityId => $community): ?>
                <tr class="<?= $community['recentActivity'] ? 'active' : null ?>">
                    <td>
                        <?= $this->element('Reports' . DS . 'community_name_cell', compact(
                            'community',
                            'communityId'
                        )) ?>
                    </td>

                    <?php $survey = $community['official_survey']; ?>
                    <td class="survey" <?= $this->Reports->sortValue($survey['invitations']) ?>>
                        <?= $survey['invitations'] ?>
                    </td>
                    <td class="survey" <?= $this->Reports->sortValue($survey['responses']) ?>>
                        <?= $survey['responses'] ?>
                    </td>
                    <td class="survey" <?= $this->Reports->sortValue($survey['responseRate']) ?>>
                        <?= $survey['responseRate'] ?>
                    </td>
                    <td class="survey" <?= $this->Reports->sortValue($survey['alignments']['vsLocal']) ?>>
                        <?php if ($survey['alignments']['vsLocal']): ?>
                            <?= $survey['alignments']['vsLocal'] ?>%
                        <?php endif; ?>
                    </td>
                    <td class="survey" <?= $this->Reports->sortValue($survey['alignments']['vsParent']) ?>>
                        <?php if ($survey['alignments']['vsParent']): ?>
                            <?= $survey['alignments']['vsParent'] ?>%
                        <?php endif; ?>
                    </td>
                    <?php foreach ($sectors as $sector): ?>
                        <td class="survey int-alignment-details" <?= $this->Reports->sortValue($survey['internalAlignment'][$sector]) ?>>
                            <?= $survey['internalAlignment'][$sector] ?>
                        </td>
                    <?php endforeach; ?>
                    <td class="survey" <?= $this->Reports->sortValue($survey['internalAlignment']['total']) ?>>
                        <?= $survey['internalAlignment']['total'] ?>
                    </td>
                    <td class="survey" <?= $this->Reports->sortValue($survey['awareOfPlanCount']) ?>>
                        <?= $survey['awareOfPlanCount'] ?>
                    </td>
                    <td class="survey" <?= $this->Reports->sortValue($survey['awareOfPlanCount']) ?>>
                        <?= $survey['unawareOfPlanCount'] ?>
                    </td>
                    <td class="survey">
                        <?= $community['presentationsGiven']['a'] ?>
                    </td>
                    <td class="survey">
                        <?= $community['presentationsGiven']['b'] ?>
                    </td>
                    <td class="survey-status">
                        <?= $survey['status'] ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="report">
    <h2>
        Community Organizations Questionnaire (Step Three)
    </h2>
    <table class="table report">
        <thead>
            <tr class="col-group-headers">
                <td>
                    <button class="survey-toggler">
                        Minimize
                    </button>
                </td>
                <td class="spacer"></td>
                <td colspan="3"></td>
                <th colspan="2">
                    PWR<sup>3</sup> Alignment
                </th>
                <th colspan="6">
                    <button>
                        Internal Alignment
                    </button>
                </th>
                <td colspan="3"></td>
            </tr>
            <tr class="general-header">
                <th>
                    Community
                </th>
                <?= $this->Reports->surveyHeader($sectors, 'organizations'); ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($report as $communityId => $community): ?>
            <tr class="<?= $community['recentActivity'] ? 'active' : null ?>">
                <td>
                    <?= $this->element('Reports' . DS . 'community_name_cell', compact(
                        'community',
                        'communityId'
                    )) ?>
                </td>

                <?php $survey = $community['organization_survey']; ?>
                <td class="survey" <?= $this->Reports->sortValue($survey['invitations']) ?>>
                    <?= $survey['invitations'] ?>
                </td>
                <td class="survey" <?= $this->Reports->sortValue($survey['responses']) ?>>
                    <?= $survey['responses'] ?>
                </td>
                <td class="survey" <?= $this->Reports->sortValue($survey['responseRate']) ?>>
                    <?= $survey['responseRate'] ?>
                </td>
                <td class="survey" <?= $this->Reports->sortValue($survey['alignments']['vsLocal']) ?>>
                    <?php if ($survey['alignments']['vsLocal']): ?>
                        <?= $survey['alignments']['vsLocal'] ?>%
                    <?php endif; ?>
                </td>
                <td class="survey" <?= $this->Reports->sortValue($survey['alignments']['vsParent']) ?>>
                    <?php if ($survey['alignments']['vsParent']): ?>
                        <?= $survey['alignments']['vsParent'] ?>%
                    <?php endif; ?>
                </td>
                <?php foreach ($sectors as $sector): ?>
                    <td class="survey int-alignment-details" <?= $this->Reports->sortValue($survey['internalAlignment'][$sector]) ?>>
                        <?= $survey['internalAlignment'][$sector] ?>
                    </td>
                <?php endforeach; ?>
                <td class="survey" <?= $this->Reports->sortValue($survey['internalAlignment']['total']) ?>>
                    <?= $survey['internalAlignment']['total'] ?>
                </td>
                <td class="survey">
                    <?= $community['presentationsGiven']['c'] ?>
                </td>
                <td class="survey">
                    <?= $community['presentationsGiven']['d'] ?>
                </td>
                <td class="survey-status">
                    <?= $survey['status'] ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php $this->append('top-html'); ?>
    <div class="modal fade" id="notes-modal" tabindex="-1" role="dialog" aria-labelledby="notes-modal-label">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="notes-modal-label">Modal title</h4>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
<?php $this->end(); ?>

<?php $this->Html->script('stupidtable.min', ['block' => 'scriptBottom']); ?>
<?php $this->Html->script('report.min', ['block' => 'scriptBottom']); ?>
<?php $this->append('buffered'); ?>
    adminReport.notes = <?= json_encode($notes) ?>;
    adminReport.init();
<?php $this->end(); ?>
