<div class="page-header">
	<h1>
		<?= $titleForLayout ?>
	</h1>
</div>

<?php
	echo $this->Form->create(false);
	echo $this->Form->input(
		'community_id',
		[
			'class' => 'form-control',
			'div' => ['class' => 'form-group'],
		]
	);
	echo $this->Form->input(
		'redirect',
		[
			'type' => 'hidden',
			'value' => $redirect
		]
	);
	echo $this->Form->end([
		'label' => 'Continue',
		'class' => 'btn btn-primary'
	]);