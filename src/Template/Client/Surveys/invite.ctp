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
                    $surveyId
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
</p>

<p>
    Enter the names and email addresses of one or more community <?= $respondentTypePlural ?> to send them survey invitations.
    <a href="#" id="sent_invitations_toggler">
        Who has already been invited?
    </a>
</p>

<div id="sent_invitations" class="alert alert-info">
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
    ?>
    <button type="button" class="remove btn btn-danger">Remove</button>
</div>

<fieldset class="form-group">
</fieldset>

<div class="form-group">
    <a href="#" class="btn btn-default" id="add_another">
        Add another
    </a>

    <?= $this->Form->button(
        'Send invitations',
        [
            'class' => 'btn btn-primary',
            'div' => false
        ]
    ) ?>
    <?= $this->Form->end() ?>
</div>

<?php $this->Html->script('client', ['block' => 'scriptBottom']); ?>
<?php $this->append('buffered'); ?>
    surveyInvitationForm.init({
        counter: 1,
        already_invited: <?= json_encode(array_values($approvedRespondents)); ?>,
        uninvited_respondents: <?= json_encode(array_values($unaddressedUnapprovedRespondents)) ?>,
    });
<?php $this->end();