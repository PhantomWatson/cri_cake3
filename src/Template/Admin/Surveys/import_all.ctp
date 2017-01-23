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
                        <?php if (! $community->official_survey->aware_of_plan_qid): ?>
                            <br />Missing <em>aware_of_plan_qid</em>
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
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php $this->append('buffered'); ?>
    $('.import_button').click(function (event) {
        event.preventDefault();
        var resultsContainer = $(this).parent('td').find('div.results');
        if (! resultsContainer.length) {
            $(this).parent('td').append('<div class="results"></div>');
            resultsContainer = $(this).parent('td').find('div.results');
        }
        importResponses($(this), resultsContainer);
    });
    $('.clear_button').click(function (event) {
        event.preventDefault();
        var confirmMsg = 'Are you sure you want to delete all responses to this questionnaire?';
        if (! confirm(confirmMsg)) {
            return;
        }
        var resultsContainer = $(this).parent('td').find('div.results');
        if (! resultsContainer.length) {
            $(this).parent('td').append('<div class="results"></div>');
            resultsContainer = $(this).parent('td').find('div.results');
        }
        clearResponses($(this), resultsContainer);
    });
<?php $this->end(); ?>
