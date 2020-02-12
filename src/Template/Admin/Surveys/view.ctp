<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Community $community
 * @var array $survey
 * @var string $titleForLayout
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<div class="survey_overview">
    <div class="panel panel-default link_survey">
        <div class="panel-heading">
            <h3 class="panel-title">
                Link
            </h3>
        </div>
        <div class="panel-body">
            <p>
                Questionnaire URL:
                <span class="survey_url">
                    <?php if ($survey['sm_url']): ?>
                        <a href="<?= $survey['sm_url'] ?>">
                            <?= $survey['sm_url'] ?>
                        </a>
                    <?php else: ?>
                        unknown
                    <?php endif; ?>
                </span>
            </p>

            <?= $this->Html->link(
                'Update link',
                [
                    'action' => 'link',
                    $community->id,
                    str_replace('_survey', '', $survey['type'])
                ],
                [
                    'class' => 'btn btn-default'
                ]
            ) ?>
        </div>
    </div>
</div>


<?php if ($survey['id']): ?>
    <?= $this->element('Surveys' . DS . 'overview') ?>
<?php endif; ?>

<?php $this->element('script', ['script' => 'admin/survey-overview']); ?>
<?php $this->append('buffered'); ?>
    surveyOverview.init({
        community_id: <?= $community->id ?>
    });
<?php $this->end(); ?>
