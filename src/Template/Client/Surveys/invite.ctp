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

<?= $this->Form->create(
    'User',
    ['id' => 'UserClientInviteForm']
) ?>

<div class="form-inline" id="invitation_fields_template" style="display: none;">
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
                'placeholder' => 'Title',
                'required' => true
            ]
        );
    ?>
    <button type="button" class="remove btn btn-danger pull-right">Remove</button>
</div>

<fieldset class="form-group">
</fieldset>

<p>
    <a href="#" class="btn btn-default pull-right" id="add_another">
        <span class="glyphicon glyphicon-plus"></span>
        Add another row
    </a>
</p>

<div class="form-group">
    <?= $this->Form->button(
        'Send invitations',
        [
            'class' => 'btn btn-primary',
            'div' => false
        ]
    ) ?>
    <a href="#" class="btn btn-default" id="save">
        Save for later
    </a>
    <span id="survey-invitation-save-status"></span>
    <?= $this->Form->end() ?>
</div>

<p>
    If you click <strong>Save for later</strong>, the information you have entered will be stored in your browser.
    After saving, you can navigate to another page and then return to this one later in order to send out invitations.
</p>

<?php
    $this->element('script', ['script' => 'client']);
    $this->element('script', ['script' => 'js.cookie.js']);
?>
<?php $this->append('buffered'); ?>
    surveyInvitationForm.init({
        counter: 1,
        already_invited: <?= json_encode(array_values($approvedRespondents)); ?>,
        uninvited_respondents: <?= json_encode(array_values($unaddressedUnapprovedRespondents)) ?>,
    });
<?php $this->end();