<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Respondent[]|\Cake\Collection\CollectionInterface $respondents
 * @var string $approvedResponseCount
 * @var string $invitationCount
 * @var string $mostRecentResponseDate
 * @var string $responseRate
 * @var mixed $surveyType
 * @var string $titleForLayout
 */
    use Cake\Validation\Validation;
?>

<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php if ($this->request->prefix == 'client'): ?>
    <p>
        <?= $this->Html->link(
            '<span class="glyphicon glyphicon-arrow-left"></span> Back to Client Home',
            [
                'prefix' => 'client',
                'controller' => 'Communities',
                'action' => 'index'
            ],
            [
                'class' => 'btn btn-default',
                'escape' => false
            ]
        ) ?>
    </p>
<?php elseif ($this->request->prefix == 'admin'): ?>
    <?= $this->element('back_to_overview_link') ?>
<?php endif; ?>

<?php if (empty($respondents)): ?>
    <p class="alert alert-info">
        No invitations have been sent out for this questionnaire.
    </p>
<?php else: ?>
    <table class="table" id="respondents-summary">
        <tbody>
            <tr>
                <th>
                    Invitations
                </th>
                <td>
                    <?= $invitationCount ?>
                </td>
            </tr>
            <tr>
                <th>
                    <?php if ($surveyType == 'official'): ?>
                        Approved
                    <?php endif; ?>
                    Responses
                </th>
                <td>
                    <?= $approvedResponseCount ?>
                </td>
            </tr>
            <tr>
                <th>
                    Response Rate
                </th>
                <td>
                    <?= $responseRate ?>
                </td>
            </tr>
            <tr>
                <th>
                    Most Recent Response
                </th>
                <td>
                    <?= $mostRecentResponseDate ?>
                </td>
            </tr>
        </tbody>
    </table>

    <?= $this->element('pagination') ?>

    <table class="table respondents">
        <thead>
            <tr>
                <th>
                    Respondent
                    <?= $this->Paginator->sort('name', 'name') ?>
                    /
                    <?= $this->Paginator->sort('email', 'email') ?>
                </th>

                <?php if ($surveyType == 'official'): ?>
                    <th>
                        <?= $this->Paginator->sort('approved', 'Approved') ?>
                    </th>
                <?php endif; ?>

                <th>
                    Completed Questionnaire
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($respondents as $respondent): ?>
                <tr>
                    <td>
                        <?= $respondent->name ? $respondent->name : '(No name)' ?>
                        <br />
                        <?php if ($respondent->title): ?>
                            <span class="title">
                                <?= $respondent->title ?>
                            </span>
                            <br />
                        <?php endif; ?>
                        <span class="email">
                            <?php if (Validation::email($respondent->email)): ?>
                                <a href="mailto:<?= $respondent->email ?>">
                                    <?= $respondent->email ?>
                                </a>
                            <?php else: ?>
                                <?= $respondent->email ? $respondent->email : '(No email)' ?>
                            <?php endif; ?>
                        </span>
                    </td>

                    <?php if ($surveyType == 'official'): ?>
                        <td class="boolean_icon">
                            <span class="glyphicon glyphicon-<?= $respondent->approved == 1 ? 'ok' : 'remove' ?>"></span>
                        </td>
                    <?php endif; ?>

                    <td class="boolean_icon">
                        <?php if (empty($respondent->responses)): ?>
                            <span class="glyphicon glyphicon-remove"></span>
                        <?php else: ?>
                            <span class="glyphicon glyphicon-ok"></span>
                            <?php $timestamp = strtotime($respondent->responses[0]['response_date']); ?>
                            <?= date('F j, Y', $timestamp) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?= $this->element('pagination') ?>

<?php endif; ?>
