<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php if ($this->request->prefix == 'admin'): ?>
    <?= $this->element('Communities/admin_header', [
        'adminHeader' => $adminHeader,
        'communityId' => $communityId,
        'surveyId' => $surveyId
    ]) ?>
    <?php $this->element('script', ['script' => 'admin']); ?>
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
    $formTemplate['inputContainerError'] = '<td>'.$formTemplate['inputContainerError'].'</td>';
    echo $this->Form->create(
        'User',
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
    <?php if ($surveyType == 'official'): ?>
        <button id="suggestions_toggler" class="btn btn-default">
            Suggestions of who to invite
        </button>
    <?php endif; ?>
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
    <?php if ($surveyType == 'official'): ?>
        <?php
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
    <?php endif; ?>
</section>

<section class="invitation-form">
    <h2>
        Enter Community <?= ucwords($surveyType) ?>s to Invite
    </h2>

    <p>
        Enter information for one or more community <?= $respondentTypePlural ?> to send them questionnaire invitations.
        Alternatively, you can
        <?php
            $filename = ($surveyType == 'official') ?
                'Community Leadership Alignment Assessment invitations.xlsx' :
                'Community Organizations Alignment Assessment invitations.xlsx';
        ?>
        <a href="/files/<?= $filename ?>">download a spreadsheet (.xlsx)</a>,
        fill it out, and then
        <button class="btn btn-link" id="toggle-upload">
            upload it
        </button>
        to automatically fill out this form.
    </p>

    <div id="upload-container">
        <p>
            Select an invitation spreadsheet to upload:
            <span id="spreadsheet-upload">
                <input type="file" id="spreadsheet-upload-input" name="files[]" accept=".xlsx" />
            </span>
        </p>
        <div class="progress" id="upload-progress">
            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                0%
            </div>
        </div>
        <p id="upload-result"></p>
    </div>

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
            <tbody class="template" style="display: none;">
                <tr id="invitation_fields_template">
                    <?php
                        echo $this->Form->input(
                            'template.name',
                            [
                                'class' => 'form-control',
                                'disabled' => true,
                                'div' => [
                                    'class' => 'form-group'
                                ],
                                'label' => false,
                                'name' => 'invitees[0][name]',
                                'placeholder' => 'Name',
                                'required' => true,
                                'type' => 'text'
                            ]
                        );
                        echo $this->Form->input(
                            'template.email',
                            [
                                'class' => 'form-control',
                                'disabled' => true,
                                'div' => [
                                    'class' => 'form-group'
                                ],
                                'label' => false,
                                'name' => 'invitees[0][email]',
                                'placeholder' => 'Email',
                                'required' => true,
                                'type' => 'email'
                            ]
                        );
                        echo $this->Form->input(
                            'template.title',
                            [
                                'class' => 'form-control',
                                'disabled' => true,
                                'div' => [
                                    'class' => 'form-group'
                                ],
                                'label' => false,
                                'name' => 'invitees[0][title]',
                                'placeholder' => 'Professional Title',
                                'required' => true
                            ]
                        );
                    ?>
                    <td>
                        <button type="button" class="remove btn btn-danger pull-right">Remove</button>
                    </td>
                </tr>
            </tbody>
            <tbody class="input">
                <?php $invitees = isset($this->request->data['invitees']) ? $this->request->data['invitees'] : []; ?>
                <?php foreach ($invitees as $key => $invitee): ?>
                    <tr>
                        <?= $this->Form->input(
                            'invitees.' . $key . '.name',
                            [
                                'class' => 'form-control',
                                'div' => [
                                    'class' => 'form-group'
                                ],
                                'label' => false,
                                'placeholder' => 'Name',
                                'required' => true,
                                'type' => 'text'
                            ]
                        ) ?>
                        <?= $this->Form->input(
                            'invitees.' . $key . '.email',
                            [
                                'class' => 'form-control',
                                'div' => [
                                    'class' => 'form-group'
                                ],
                                'label' => false,
                                'placeholder' => 'Email',
                                'required' => true,
                                'type' => 'email'
                            ]
                        ) ?>
                        <?= $this->Form->input(
                            'invitees.' . $key . '.title',
                            [
                                'class' => 'form-control',
                                'div' => [
                                    'class' => 'form-group'
                                ],
                                'label' => false,
                                'placeholder' => 'Professional Title',
                                'required' => true
                            ]
                        ) ?>
                        <td>
                            <button type="button" class="remove btn btn-danger pull-right">Remove</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
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

<?php $this->append('top-html'); ?>
    <div class="modal fade" id="spreadsheet-modal" tabindex="-1" role="dialog" aria-labelledby="spreadsheet-modalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="spreadsheet-modalLabel">
                        Using an Invitation Spreadsheet
                    </h4>
                </div>
                <div class="modal-body">
                    If you prefer, you could
                    <a href="/files/Community Leadership Alignment Assessment invitations.xlsx" class="download">
                        download a spreadsheet (.xlsx)
                    </a>
                    and fill in the names and email addresses of the community officials
                    that you would like to send questionnaire invitations to. If you then upload the
                    completed spreadsheet, this form will automatically be filled out and
                    ready for you to submit.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php $this->end(); ?>

<section class="invitation-form">
    <h2>
        Done
    </h2>
    <div class="form-group">
        <?= $this->Form->input(
            'Send invitations',
            [
                'class' => 'btn btn-primary',
                'div' => false,
                'id' => 'invitations-send',
                'name' => 'submit_mode',
                'type' => 'submit'
            ]
        ) ?>
        <?= $this->Form->input(
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
    </p>
</section>

<?= $this->Form->end() ?>
<?php $this->Form->templates($bootstrapFormTemplate); ?>

<?php
    $this->element('script', ['script' => 'client']);
    $this->element('script', ['script' => 'js.cookie.js']);
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

