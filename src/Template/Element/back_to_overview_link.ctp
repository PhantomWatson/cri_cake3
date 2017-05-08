<p>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> ' .
        'Back to Questionnaire Overview',
        [
            'prefix' => 'admin',
            'controller' => 'Surveys',
            'action' => 'view',
            $communityId,
            $surveyType
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
</p>
