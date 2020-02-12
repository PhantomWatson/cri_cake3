<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Community $community
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php
    echo $this->Form->create(
        $community,
        ['id' => 'CommunityAdminEditForm']
    );
    echo $this->Form->input('name');
    echo $this->Form->input(
        'local_area_id',
        [
            'empty' => true,
            'label' => 'Local Area (e.g. city)',
            'options' => $areas
        ]
    );
    echo $this->Form->input(
        'parent_area_id',
        [
            'empty' => true,
            'label' => 'Wider Area (e.g. county)',
            'options' => $areas
        ]
    );
    $scores = [1, 2, 3, 4, 5];
?>
<div id="score-input-wrapper">
    <?= $this->Form->input(
        'score',
        [
            'escape' => false,
            'label' => [
                'text' => 'Stage / PWR<sup>3</sup> &trade; Score',
                'escape' => false
            ],
            'options' => array_combine($scores, $scores),
            'type' => 'select'
        ]
    ) ?>
</div>

<p id="score-editing-note">
    <?php if ($this->request->prefix == 'admin' && $community->slug): ?>
        <strong>Note:</strong>
        You're encouraged to edit this community's score through its
        <?= $this->Html->link(
            'progress page',
            [
                'prefix' => 'admin',
                'controller' => 'Communities',
                'action' => 'progress',
                $community->slug
            ]
        ) ?>, which provides detailed information to help advise you.
    <?php endif; ?>
</p>

<div class="custom_radio">
    <?= $this->Form->input(
        'public',
        [
            'escape' => false,
            'label' => 'Who should be able to see this community\'s performance report?',
            'legend' =>  false,
            'options' =>  [
                1 => '<strong>Public:</strong> Everyone',
                0 => '<strong>Private:</strong> Only the client, admins, and appropriate consultants'
            ],
            'separator' => '<br />',
            'type'      =>  'radio'
        ]
    ) ?>

    <?= $this->Form->input('intAlignmentAdjustment', [
        'label' => 'Internal Alignment Adjustment',
        'max' => '99.99',
        'min' => '0'
    ]) ?>
    <?= $this->Form->input('intAlignmentThreshold', [
        'label' => 'Internal Alignment Threshold',
        'max' => '99.99',
        'min' => '0'
    ]) ?>
</div>

<?php
    $label = $this->request->getParam('action') == 'add'
        ? 'Add Community'
        : 'Update Community';
    echo $this->Form->button(
        $label,
        ['class' => 'btn btn-primary']
    );
    echo $this->Form->end();

    $this->element('script', ['script' => 'form-protector']);
    $this->element('script', ['script' => 'admin/community-form']);
?>

<?php $this->append('buffered'); ?>
    communityForm.init({
        community_id: <?= isset($communityId) ? $communityId : 'null' ?>,
        areaTypes: <?= json_encode($areaTypes) ?>
    });
    formProtector.protect('CommunityAdminEditForm', {});
<?php $this->end();
