<div class="page-header">
	<h1>
		<?= $titleForLayout ?>
	</h1>
</div>

<p>
	<?= $this->Html->link(
		'Change Password',
		['action' => 'change_password'],
		['class' => 'btn btn-primary']
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
		'email',
		[
			'class' => 'form-control',
			'div' => ['class' => 'form-group']
		]
	);
	echo $this->Form->end([
		'label' => 'Update',
		'class' => 'btn btn-primary'
	]);
?>