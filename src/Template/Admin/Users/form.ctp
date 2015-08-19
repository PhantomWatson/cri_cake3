<div class="page-header">
	<h1>
		<?= $titleForLayout ?>
	</h1>
</div>

<p>
	<?= $this->Html->link(
		'<span class="glyphicon glyphicon-arrow-left"></span> Back to Users',
		[
			'prefix' => 'admin',
			'controller' => 'Users',
			'action' => 'index'
		],
		[
			'class' => 'btn btn-default',
			'escape' => false
		]
	) ?>
</p>

<?php
	echo $this->Form->create($user);
	echo $this->Form->input(
		'name',
		[
			'class' => 'form-control',
			'div' => ['class' => 'form-group']
		]
	);
	echo $this->Form->input(
		'title',
		[
			'class' => 'form-control',
			'div' => ['class' => 'form-group'],
			'label' => 'Job Title'
		]
	);
	echo $this->Form->input(
		'organization',
		[
			'class' => 'form-control',
			'div' => ['class' => 'form-group']
		]
	);
	echo $this->Form->input(
		'email',
		[
			'class' => 'form-control',
			'div' => ['class' => 'form-group']
		]
	);
	echo $this->Form->input(
		'phone',
		[
			'class' => 'form-control',
			'div' => ['class' => 'form-group']
		]
	);

	if ($this->request->action == 'add' && $this->request->prefix == 'admin') {
		echo $this->Form->input(
			'password',
			[
				'autocomplete' => 'off',
				'class' => 'form-control',
				'div' => ['class' => 'form-group']
			]
		);
		echo $this->Form->input(
			'confirm_password',
			[
				'class' => 'form-control',
				'div' => ['class' => 'form-group'],
				'label' => 'Confirm password',
				'type' => 'password'
			]
		);
	} elseif ($this->request->action == 'edit' && $this->request->prefix == 'admin') {
		echo $this->Form->input(
			'new_password',
			[
				'autocomplete' => 'off',
				'class' => 'form-control',
				'div' => ['class' => 'form-group'],
				'label' => 'Change password',
				'required' => false
			]
		);
		echo $this->Form->input(
			'confirm_password',
			[
				'class' => 'form-control',
				'div' => ['class' => 'form-group'],
				'label' => 'Repeat new password'
			]
		);
	}

	echo $this->Form->input(
		'role',
		[
			'after' => '<span class="note">Admins automatically have access to all communities and site functions</span>',
			'class' => 'form-control',
			'div' => ['class' => 'form-group'],
			'options' => $roles
		]
	);
?>

<div id="consultant_communities">
	<?php
		echo $this->Form->input(
			'all_communities',
			[
				'before' => '<span class="fake_label">Which communities should this consultant have access to?</span><br />',
				'div' => ['class' => 'form-group all_communities'],
				'legend' =>  false,
				'options' =>  [
					1 => 'All communities',
					0 => 'Only specific communities'
				],
				'separator' => '<br />',
				'type'      =>  'radio'
			]
		);
		echo $this->Form->input(
			'community',
			[
				'class' => 'form-control',
				'div' => ['class' => 'form-group'],
				'empty' => 'Choose one or more communities to allow this user access to...',
				'label' => false,
				'options' => $communities
			]
		);
	?>
</div>

<div id="client_communities">
	<?php
		echo $this->Form->input(
			'ClientCommunity.0',
			[
				'class' => 'form-control',
				'div' => ['class' => 'form-group'],
				'empty' => 'Choose a community to assign this client to...',
				'label' => false,
				'options' => $communities,
				'required' => false
			]
		);
	?>
</div>

<?php
	$label = ($this->action == 'admin_add') ? 'Add User' : 'Update';
	echo $this->Form->end([
		'label' => $label,
		'class' => 'btn btn-primary'
	]);
	$this->Html->script('admin', ['block' => 'scriptBottom']);
?>
<?php $this->append('buffered'); ?>
	adminUserEdit.init({
		selected_communities: <?= $this->Js->object($selectedCommunities) ?>
	});
<?php $this->end(); ?>