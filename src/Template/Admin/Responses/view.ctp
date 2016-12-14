<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<div id="admin-responses-view">
    <?php if ($responses): ?>
        <section>
            <h2>
                Summary
            </h2>
            <p>
                <?= number_format(count($responses)) ?>
                complete responses as of
                <?= $survey->responses_checked->format('F j') ?><sup><?= $survey->responses_checked->format('S') ?></sup>,
                <?= $survey->responses_checked->format('Y') ?>
            </p>
            <?php $area = $community->local_area ?: $community->parent_area; ?>
            <table class="table" id="responses-summary">
                <thead>
                    <tr>
                        <th>
                            <?= $area->name ?>
                        </th>
                        <?php foreach ($sectors as $sector): ?>
                            <th>
                                <?= ucfirst($sector) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>
                            Actual PWR<sup>3</sup> Ranking of <?= $area->name ?>
                        </th>
                        <?php foreach ($sectors as $sector): ?>
                            <td>
                                <?= $area->{$sector . '_rank'} ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>
                            Response Order
                        </th>
                        <?php foreach ($sectors as $sector): ?>
                            <td>
                                <?= $rankOrder[$sector] ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>
                            Internal Alignment Scores
                            <br />
                            <span class="note">
                                Lower numbers indicate that respondents are better-aligned with each other for that
                                category relative to others
                            </span>
                        </th>
                        <?php foreach ($sectors as $sector): ?>
                            <td>
                                <?= round($internalAlignment[$sector], 3) ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </section>

        <section>
            <h2>
                PWR<sup>3</sup> Alignment
                <button class="btn btn-sm btn-default" id="toggle-table-scroll"></button>
                <button class="btn btn-sm btn-default" id="show-respondents" data-label="show"></button>
            </h2>
            <p>
                These are the currently known responses to
                <strong>
                    <?= $community->name ?>'s
                    community
                    <?= $survey->type == 'official' ? 'leadership' : 'organization' ?>
                </strong>
                questionnaire. Incomplete responses are excluded, and recent responses may have not been imported yet.
            </p>

            <p>
                Click on <span class="glyphicon glyphicon-search"></span> to view <em>all</em> of the questions and answers for a response.
            </p>
            <div>
                <ul class="nav nav-tabs" role="tablist">
                    <li>
                        Compared to:
                    </li>
                    <?php if ($community->local_area): ?>
                        <li role="presentation">
                            <a href="#vsLocalArea" aria-controls="vsLocalArea" role="tab" data-toggle="tab">
                                <?= $community->local_area['name'] ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($community->parent_area): ?>
                        <li role="presentation">
                            <a href="#vsParentArea" aria-controls="vsParentArea" role="tab" data-toggle="tab">
                                <?= $community->parent_area['name'] ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="tab-content">
                    <?php
                        function getSortArrow($sortField, $params) {
                            if (isset($params['named']['sort']) && $params['named']['sort'] == $sortField) {
                                $direction = strtolower($params['named']['direction']) == 'desc' ? 'up' : 'down';
                                return '<span class="glyphicon glyphicon-arrow-'.$direction.'" aria-hidden="true"></span>';
                            }
                            return '';
                        }
                    ?>
                    <?php if ($community->local_area): ?>
                        <div role="tabpanel" class="tab-pane active" id="vsLocalArea">
                            <?= $this->element('Respondents'.DS.'admin_table', [
                                'area' => $community->local_area,
                                'alignmentField' => 'local_area_pwrrr_alignment'
                            ]) ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($community->parent_area): ?>
                        <div role="tabpanel" class="tab-pane" id="vsParentArea">
                            <?= $this->element('Respondents'.DS.'admin_table', [
                                'area' => $community->parent_area,
                                'alignmentField' => 'parent_area_pwrrr_alignment'
                            ]) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section>
            <h2>
                Internal Alignment
            </h2>
            <table class="table" id="internal-alignment-breakdown">
                <thead>
                    <tr>
                        <th>
                            Sector
                        </th>
                        <th>
                            Internal Alignment of Approved Responses
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sectors as $sector): ?>
                        <tr>
                            <th>
                                <?= ucwords($sector) ?>
                            </th>
                            <td>
                                <?= round($internalAlignment[$sector], 3) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>
                            Sum
                        </td>
                        <td>
                            <?= round($internalAlignmentSum, 3) ?>
                            <span class="alignment-note <?= $internalAlignmentClass ?>">
                                <span class="glyphicon"></span>
                                <?= str_replace('-', ' ', $internalAlignmentClass) ?>
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </section>
    <?php else: ?>
        <p class="alert alert-info">
            No responses have been imported yet.
        </p>
    <?php endif; ?>
</div>

<?php $this->append('top-html'); ?>
    <div class="modal fade" id="full-response-modal" tabindex="-1" role="dialog" aria-labelledby="full-response-modalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="full-response-modalLabel">
                        Full Response
                    </h4>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php $this->end(); ?>

<?php $this->append('buffered'); ?>
    adminViewResponses.init();
<?php $this->end(); ?>
