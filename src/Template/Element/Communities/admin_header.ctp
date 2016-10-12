<?php
    use Cake\Routing\Router;
?>

<form class="form-inline" id="admin-header">
    <select class="form-control form-inline" id="admin-header-community">
        <?php if (! $adminHeader['selectedCommunity']): ?>
            <option value="">
                Select community...
            </option>
        <?php endif; ?>
        <?php foreach ($adminHeader['communities'] as $ahCommunityId => $ahCommunityName): ?>
            <?php if ($adminHeader['selectedCommunity'] == $ahCommunityId): ?>
                <option value="<?= $ahCommunityId ?>" selected="selected">
                    <?= $ahCommunityName ?>
                </option>
            <?php else: ?>
                <option value="<?= $ahCommunityId ?>">
                    <?= $ahCommunityName ?>
                </option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>

    <select class="form-control form-inline" id="admin-header-page">
        <option value="">
            Go to...
        </option>

        <optgroup label="Community">
            <?php foreach ($adminHeader['communityPages'] as $label => $url): ?>
                <option value="<?= Router::url($url) ?>">
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
                    <option value="<?= Router::url($url) ?>">
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

<?php $this->append('buffered'); ?>
    var surveyIds = <?= json_encode($adminHeader['surveyIds']) ?>;
    adminHeader.init(surveyIds);
<?php $this->end(); ?>
