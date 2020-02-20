<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $allRespondents
 * @var mixed $approvedRespondents
 * @var array $invitees
 * @var string $surveyId
 * @var mixed $surveyType
 * @var string $titleForLayout
 * @var mixed $unaddressedUnapprovedRespondents
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php if ($this->request->prefix == 'admin'): ?>
    <?= $this->element('back_to_overview_link') ?>
<?php endif; ?>

<p>
    <?php if ($this->request->prefix != 'admin'): ?>
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
    <?php endif; ?>
</p>

<?php
    $bootstrapFormTemplate = include(ROOT.DS.'config'.DS.'bootstrap_form.php');
    $formTemplate = $bootstrapFormTemplate;
    $formTemplate['inputContainer'] = '<td>'.$formTemplate['inputContainer'].'</td>';
    $formTemplate['inputContainerError'] = '<td>{{error}}'.$formTemplate['inputContainerError'].'</td>';
    echo $this->Form
        ->setValueSources('query')
        ->create(
            null,
            [
                'id' => 'UserClientInviteForm',
                'enctype' => 'multipart/form-data'
            ]
        );
    $this->Form->templates($formTemplate);
?>

<section class="invitation-form">
    <h2>
        Before You Invite
    </h2>
    <button id="sent_invitations_toggler" class="btn btn-default">
        Who has already been invited?
    </button>
    <button id="suggestions_toggler" class="btn btn-default">
        Suggestions of who to invite
    </button>
    <div id="sent_invitations" class="well">
        <?php if (empty($allRespondents)): ?>
            No invitations have been sent yet.
        <?php else: ?>
            <?php if (! empty($approvedRespondents)): ?>
                <p>
                    Responses from the following email <?= __n('address', 'addresses', count($approvedRespondents)) ?> have been invited or approved:
                </p>
                <ul>
                    <?php foreach ($approvedRespondents as $respondentId => $respondentEmail): ?>
                        <li>
                            <?= $respondentEmail ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (! empty($unaddressedUnapprovedRespondents)): ?>
                <p>
                    <?php if (! empty($approvedRespondents)): ?>
                        Additionally, <?= __n('an unapproved questionnaire response has', 'unapproved questionnaire responses have', count($unaddressedUnapprovedRespondents)) ?>
                    <?php else: ?>
                        <?= __n('An unapproved questionnaire response has', 'Unapproved questionnaire responses have', count($unaddressedUnapprovedRespondents)) ?>
                    <?php endif; ?>
                    been received from the following, who <?= __n('was not sent an invitation', 'were not sent invitations', count($unaddressedUnapprovedRespondents)) ?>:
                </p>
                <ul>
                    <?php foreach ($unaddressedUnapprovedRespondents as $respondentId => $respondentEmail): ?>
                        <li>
                            <?= $respondentEmail ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
        if ($surveyType == 'official') {
            $suggestions = [
                'Mayor',
                'City Clerk',
                'City Treasurer',
                'City Council',
                'City Planning Director',
                'Director of Community Development',
                'Planning Commission',
                'Redevelopment Commission',
                'Economic Development Commission',
                'County Commissioner',
                'County Council'
            ];
        } else {
            $suggestions = [
                'Nonprofit Organizations',
                'Service Organizations',
                'Community Volunteer Groups',
                'Civic Centers'
            ];
        }
    ?>
    <div id="invitation_suggestions" class="well">
        <ul>
            <?php foreach ($suggestions as $suggestion): ?>
                <li>
                    <?= $suggestion ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="invitation-form">
    <h2>
        Enter Community <?= ucwords($surveyType) ?>s to Invite
    </h2>

    <div>
        <table>
            <thead>
                <tr>
                    <th>
                        Name
                    </th>
                    <th>
                        Email
                    </th>
                    <th>
                        Professional Title
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php for ($n = 0; $n < 20; $n++): ?>
                    <tr>
                        <?php
                            $this->Form->unlockField("invitees.$n.name");
                            $this->Form->unlockField("invitees.$n.email");
                            $this->Form->unlockField("invitees.$n.title");
                        ?>
                        <?= $this->Form->control(
                            "invitees.$n.name",
                            [
                                'class' => 'form-control',
                                'label' => false,
                                'placeholder' => 'Name',
                                'required' => true,
                                'type' => 'text',
                                'value' => isset($invitees[$n]['name']) ? $invitees[$n]['name'] : null
                            ]
                        ) ?>
                        <?= $this->Form->control(
                            "invitees.$n.email",
                            [
                                'class' => 'form-control',
                                'label' => false,
                                'placeholder' => 'Email',
                                'required' => true,
                                'type' => 'email',
                                'value' => isset($invitees[$n]['email']) ? $invitees[$n]['email'] : null
                            ]
                        ) ?>
                        <?= $this->Form->control(
                            "invitees.$n.title",
                            [
                                'class' => 'form-control',
                                'label' => false,
                                'placeholder' => 'Professional Title',
                                'required' => true,
                                'value' => isset($invitees[$n]['title']) ? $invitees[$n]['title'] : null
                            ]
                        ) ?>
                        <td>
                            <button type="button" class="remove btn btn-danger pull-right">Remove</button>
                        </td>
                    </tr>
                <?php endfor; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">
                        <p>
                            <button class="btn btn-default" id="add_another">
                                <span class="glyphicon glyphicon-plus"></span>
                                Add another row
                            </button>
                        </p>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</section>

<section class="invitation-form">
    <h2>
        Done
    </h2>
    <div class="form-group">
        <?= $this->Form->control(
            'Send invitations',
            [
                'class' => 'btn btn-primary',
                'div' => false,
                'id' => 'invitations-send',
                'name' => 'submit_mode',
                'type' => 'submit'
            ]
        ) ?>
        <?= $this->Form->control(
            'Save for later',
            [
                'class' => 'btn btn-default',
                'div' => false,
                'id' => 'invitations-save',
                'name' => 'submit_mode',
                'type' => 'submit'
            ]
        ) ?>
    </div>
    <p>
        If you <strong>save this form for later</strong>, no invitations will be sent out at this time.
        You will be able to return to this page at a later time to review and send out invitations to participate in
        this questionnaire.
        <button id="clear-data" class="btn btn-link">
            Clear saved data
        </button>
        <span id="clear-data-results"></span>
    </p>
</section>

<?= $this->Form->end() ?>
<?php $this->Form->templates($bootstrapFormTemplate); ?>

<?php
    $this->element('script', ['script' => 'client/survey-invitation-form']);
    $this->element('script', ['script' => 'form-protector']);
    $this->element('script', ['script' => 'jquery.ui.widget']);
    $this->element('script', ['script' => 'jquery.fileupload']);
?>

<?php $this->append('buffered'); ?>
    surveyInvitationForm.init({
        counter: 1,
        already_invited: <?= json_encode(array_values($approvedRespondents)); ?>,
        uninvited_respondents: <?= json_encode(array_values($unaddressedUnapprovedRespondents)) ?>,
        surveyId: <?= $surveyId ?>
    });
<?php $this->end(); ?>

