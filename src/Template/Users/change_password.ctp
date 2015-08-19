<div class="page-header">
	<h1>
		<?= $titleForLayout ?>
	</h1>
</div>

<p>
	<?= $this->Html->link(
		'<span class="glyphicon glyphicon-arrow-left glyphicon-white"></span> Back to Account',
		['action' => 'edit'],
		[
			'class' => 'btn btn-primary',
			'escape' => false
		]
	) ?>
</p>

<?php
	echo $this->Form->create($user);
	echo $this->Form->input(
		'password',
		[
			'autocomplete' => 'off',
			'class' => 'form-control',
			'div' => ['class' => 'form-group'],
			'label' => 'Change password'
		]
	);
	echo $this->Form->input(
		'confirm_password',
		[
			'autocomplete' => 'off',
			'class' => 'form-control',
			'div' => ['class' => 'form-group'],
			'label' => 'Repeat new password',
			'type' => 'password'
		]
	);
	echo $this->Form->end([
		'class' => 'btn btn-primary',
		'label' => 'Submit'
	]);