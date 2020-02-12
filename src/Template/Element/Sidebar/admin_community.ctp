<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Survey $survey
 * @var array $adminHeader
 */
    if (! isset($communityId)) {
        $communityId = isset($community->id) ? $community->id : null;
    }
    if (! isset($surveyId)) {
        $surveyId = isset($survey->id) ? $survey->id : null;
    }
?>
<form id="admin-sidebar-community">
    <select class="form-control" name="community">
        <option value="">
            Select community...
        </option>
        <?php foreach ($adminHeader['communities'] as $community): ?>
            <option value="<?= $community->id ?>">
                <?= $community->name ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select class="form-control" name="page">
        <option value="">
            Go to...
        </option>

        <optgroup label="Community">
            <?php foreach ($adminHeader['communityPages'] as $label => $url): ?>
                <option value="<?= $url ?>">
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </optgroup>

        <?php
        $surveyTypes = [
            'Officials Questionnaire' => 'official',
            'Organizations Questionnaire' => 'organization',
        ];
        ?>
        <?php foreach ($surveyTypes as $label => $surveyType): ?>
            <optgroup label="<?= $label ?>" data-survey-type="<?= $surveyType ?>">
                <?php foreach ($adminHeader['surveyPages'] as $label => $url): ?>
                    <option value="<?= str_replace('{survey-type}', $surveyType, $url) ?>">
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn btn-default">
        Go
        <span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
    </button>
</form>

<?php $this->element('script', ['script' => 'admin/community-nav']); ?>
<?php $this->append('buffered'); ?>
    var surveyIds = <?= json_encode($adminHeader['surveyIds']) ?>;
    adminHeader.init({
        communityId: <?= json_encode($communityId) ?>,
        currentUrl: <?= json_encode($adminHeader['currentUrl']) ?>,
        surveyId: <?= json_encode($surveyId) ?>,
        surveyIds: surveyIds,
        slugs: <?= json_encode($adminHeader['slugs']) ?>
    });
<?php $this->end(); ?>
