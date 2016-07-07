<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?php
        if ($this->request->prefix == 'admin') {
            echo $this->Html->link(
                '<span class="glyphicon glyphicon-arrow-left"></span> Back to Questionnaire Overview',
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
            );
        } else {
            echo $this->Html->link(
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
            );
        }
    ?>
    <button id="sent_invitations_toggler" class="btn btn-default">
        Who has already been invited?
    </button>
    <?php if ($surveyType == 'official'): ?>
        <button id="suggestions_toggler" class="btn btn-default">
            Suggestions of who to invite
        </button>
    <?php endif; ?>
</p>

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

<p>
    Enter information for one or more community <?= $respondentTypePlural ?> to send them questionnaire invitations.
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

<div class="well">
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
        <tbody class="input"></tbody>
        <tfoot>
            <tr>
                <td colspan="4">
                    <p>
                        <button class="btn btn-default" id="add_another">
                            <span class="glyphicon glyphicon-plus"></span>
                            Add another row
                        </button>
                        <button class="btn btn-default" id="toggle-upload">
                            <span class="glyphicon glyphicon-upload"></span>
                            Upload invitation spreadsheet
                        </button>
                        <button id="show-spreadsheet-modal" class="btn btn-link" data-toggle="modal" data-target="#spreadsheet-modal">
                            <span class="glyphicon glyphicon-question-sign" title="Learn more about using an invitation spreadsheet" aria-hidden="true"></span>
                            <span class="sr-only">
                                Learn more about using an invitation spreadsheet
                            </span>
                        </button>
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
                </td>
            </tr>
        </tfoot>
    </table>
</div>

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

<div class="well">
    <p>
        If you click <strong>Save for later</strong>, the information you have entered will be stored in your browser.
        After saving, you can navigate to another page and then return to this one later in order to send out your saved invitations.
    </p>
    <button class="btn btn-default" id="save">
        Save for later
    </button>
    <button class="btn btn-default" id="load">
        Load saved data
    </button>
    <span id="survey-invitation-save-status"></span>
</div>

<div class="form-group">
    <?= $this->Form->button(
        'Send invitations',
        [
            'class' => 'btn btn-primary',
            'div' => false
        ]
    ) ?>
    <?php
        echo $this->Form->end();
        $this->Form->templates($bootstrapFormTemplate);
    ?>
</div>

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

<?php if (isset($_GET['debugcookie'])): ?>
    <h2>
        Saved form data:
    </h2>
    <pre id="results"></pre>

    <?php $this->append('buffered'); ?>
        var cookieData = Cookies.get('invitationFormData');
        if (typeof cookieData == 'undefined' || cookieData.length === 0) {
            $('#results').html('No saved data was found');
        } else {
            $('#results').html(JSON.stringify(cookieData));
        }
    <?php $this->end(); ?>
<?php endif; ?>
