<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?php
        if ($this->request->prefix == 'admin') {
            echo $this->Html->link(
                '<span class="glyphicon glyphicon-arrow-left"></span> Back to Survey Overview',
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
    <a href="#" id="sent_invitations_toggler" class="btn btn-default">
        Who has already been invited?
    </a>
    <a href="#" id="suggestions_toggler" class="btn btn-default">
        Suggestions of who to invite
    </a>
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
                    Additionally, <?= __n('an unapproved survey response has', 'unapproved survey responses have', count($unaddressedUnapprovedRespondents)) ?>
                <?php else: ?>
                    <?= __n('An unapproved survey response has', 'Unapproved survey responses have', count($unaddressedUnapprovedRespondents)) ?>
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
    $suggestions = [
        'Mayor',
        'City Clerk',
        'City Treasurer',
        'City Council',
        'Board of Public Words',
        'City Planning Director',
        'Director of Community Development',
        'Parks & Recreation Director',
        'Street Superintendent',
        'Police Chief',
        'Planning Commission',
        'Redevelopment Commission',
        'Economic Development Commission',
        'Utility Rate Advisory Board',
        'Housing Authority',
        'School Corporation Officials',
        'Public Library Board Officials',
        'County Commissioner',
        'County Council',
        'State Senators and Representatives'
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

<p>
    Enter information for one or more community <?= $respondentTypePlural ?> to send them survey invitations.
</p>

<?php
    $bootstrapFormTemplate = include(ROOT.DS.'config'.DS.'bootstrap_form.php');
    $formTemplate = $bootstrapFormTemplate;
    $formTemplate['inputContainer'] = '<td>'.$formTemplate['inputContainer'].'</td>';
    $formTemplate['inputContainerError'] = '<td>'.$formTemplate['inputContainerError'].'</td>';
    echo $this->Form->create(
        'User',
        ['id' => 'UserClientInviteForm']
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
        <tr class="form-inline" id="invitation_fields_template">
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
                <a href="#" class="btn btn-default" id="add_another">
                    <span class="glyphicon glyphicon-plus"></span>
                    Add another row
                </a>
            </td>
        </tr>
    </tfoot>
</table>
</div>

<div class="well">
    <p>
        If you click <strong>Save for later</strong>, the information you have entered will be stored in your browser.
        After saving, you can navigate to another page and then return to this one later in order to send out your saved invitations.
    </p>
    <a href="#" class="btn btn-default" id="save">
        Save for later
    </a>
    <a href="#" class="btn btn-default" id="load">
        Load saved data
    </a>
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
?>

<?php $this->append('buffered'); ?>
    surveyInvitationForm.init({
        counter: 1,
        already_invited: <?= json_encode(array_values($approvedRespondents)); ?>,
        uninvited_respondents: <?= json_encode(array_values($unaddressedUnapprovedRespondents)) ?>,
    });
<?php $this->end(); ?>