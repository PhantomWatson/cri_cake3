<?php
/**
 * @var \App\View\AppView $this
 * @var array $community
 * @var mixed $surveyType
 */
?>
<div class="dropdown">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <?= $community[$surveyType]['status'] ?> <span class="caret"></span>
    </button>
    <?php if (isset($community[$surveyType]['sm_id']) && $community[$surveyType]['sm_id']): ?>
        <ul class="dropdown-menu" role="menu">
            <li class="dropdown-header">
                <?php
                $alignmentsDisplayed = [];
                if ($community[$surveyType]['alignment_vs_local']) {
                    $alignmentsDisplayed[] = 'PWR<sup>3</sup> Alignment vs local area: ' . $community[$surveyType]['alignment_vs_local'] . '%';
                }
                if ($community[$surveyType]['alignment_vs_parent']) {
                    $alignmentsDisplayed[] = 'PWR<sup>3</sup> Alignment vs wider area: ' . $community[$surveyType]['alignment_vs_parent'] . '%';
                }
                if (empty($alignmentsDisplayed)) {
                    echo 'Alignment: Not calculated';
                } else {
                    echo implode('<br />', $alignmentsDisplayed);
                }
                ?>
            </li>

            <?php if ($community[$surveyType]['respondents_last_modified_date']): ?>
                <li class="dropdown-header">
                    Last response:
                    <?php
                    $date = $community[$surveyType]['respondents_last_modified_date'];
                    echo $this->Time->format($date, 'M/d/YYYY', false, 'America/New_York');
                    ?>
                </li>
            <?php endif; ?>

            <li role="separator" class="divider"></li>

            <li>
                <?= $this->Html->link(
                    '<span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> Overview',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Surveys',
                        'action' => $community[$surveyType]['sm_id'] ? 'view' : 'link',
                        $community['slug'],
                        str_replace('_survey', '', $surveyType)
                    ],
                    ['escape' => false]
                ) ?>
            </li>
            <li>
                <?= $this->Html->link(
                    '<span class="glyphicon glyphicon-link" aria-hidden="true"></span> Questionnaire link',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Surveys',
                        'action' => 'link',
                        $community['slug'],
                        str_replace('_survey', '', $surveyType)
                    ],
                    ['escape' => false]
                ) ?>
            </li>
            <li>
                <?php
                $label =
                    '<span class="glyphicon glyphicon-' .
                    ($community[$surveyType]['active'] ? 'remove-circle' : 'ok-circle') .
                    '" aria-hidden="true"></span> ' .
                    ($community[$surveyType]['active'] ? 'Deactivate' : 'Activate');
                echo $this->Html->link(
                    $label,
                    [
                        'prefix' => 'admin',
                        'controller' => 'Surveys',
                        'action' => 'activate',
                        $community[$surveyType]['id']
                    ],
                    ['escape' => false]
                );
                ?>
            </li>
            <?php if ($community[$surveyType]['active']): ?>
                <li>
                    <?= $this->Html->link(
                        '<span class="glyphicon glyphicon-send" aria-hidden="true"></span> Invitations',
                        [
                            'prefix' => 'admin',
                            'controller' => 'Surveys',
                            'action' => 'invite',
                            $community[$surveyType]['id']
                        ],
                        ['escape' => false]
                    ) ?>
                </li>
                <li>
                    <?= $this->Html->link(
                        '<span class="glyphicon glyphicon-alert" aria-hidden="true"></span> Reminders',
                        [
                            'prefix' => 'admin',
                            'controller' => 'Surveys',
                            'action' => 'remind',
                            $community[$surveyType]['id']
                        ],
                        ['escape' => false]
                    ) ?>
                </li>
            <?php endif; ?>
            <li>
                <?= $this->Html->link(
                    '<span class="glyphicon glyphicon-user" aria-hidden="true"></span> Respondents',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Respondents',
                        'action' => 'view',
                        $community[$surveyType]['id']
                    ],
                    ['escape' => false]
                ) ?>
            </li>
            <li>
                <?= $this->Html->link(
                    '<span class="glyphicon glyphicon-scale" aria-hidden="true"></span> Alignment',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Responses',
                        'action' => 'view',
                        $community[$surveyType]['id']
                    ],
                    ['escape' => false]
                ) ?>
            </li>
        </ul>
    <?php else: ?>
        <ul class="dropdown-menu" role="menu">
            <li>
                <?= $this->Html->link(
                    '<span class="glyphicon glyphicon-link" aria-hidden="true"></span> Link to SurveyMonkey questionnaire',
                    [
                        'prefix' => 'admin',
                        'controller' => 'Surveys',
                        'action' => 'link',
                        $community['slug'],
                        str_replace('_survey', '', $surveyType)
                    ],
                    ['escape' => false]
                ) ?>
            </li>
        </ul>
    <?php endif; ?>
</div>
