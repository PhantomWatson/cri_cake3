<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<table class="table">
    <thead>
        <tr>
            <th>
                Community
            </th>
            <th>
                Official Questionnaire
            </th>
            <th>
                Org Questionnaire
            </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($communities as $community): ?>
            <tr>
                <th>
                    <?= $community->name ?>
                </th>
                <td>
                    <?php if (! $community->official_survey): ?>
                        Not set up
                    <?php elseif (! $community->official_survey->active): ?>
                        Inactive
                    <?php else: ?>
                        <button class="btn btn-default clear_button" data-survey-id="<?= $community->official_survey->id ?>">
                            Clear
                        </button>
                        <button class="btn btn-default import_button" data-survey-id="<?= $community->official_survey->id ?>">
                            Import
                        </button>
                        <?php if (! $community->official_survey->pwrrr_qid): ?>
                            <div class="text-danger">Missing <em>pwrrr_qid</em></div>
                        <?php endif; ?>
                        <?php if (! $community->official_survey->aware_of_plan_qid): ?>
                            <div class="text-danger">Missing <em>aware_of_plan_qid</em></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (! $community->organization_survey): ?>
                        Not set up
                    <?php elseif (! $community->organization_survey->active): ?>
                        Inactive
                    <?php else: ?>
                        <button class="btn btn-default clear_button" data-survey-id="<?= $community->organization_survey->id ?>">
                            Clear
                        </button>
                        <button class="btn btn-default import_button" data-survey-id="<?= $community->organization_survey->id ?>">
                            Import
                        </button>
                        <?php if (! $community->organization_survey->pwrrr_qid): ?>
                            <div class="text-danger">Missing <em>pwrrr_qid</em></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php $this->element('script', ['script' => 'admin/import-all-surveys']); ?>
<?php $this->append('buffered'); ?>
    importAllSurveys.init();
<?php $this->end(); ?>
