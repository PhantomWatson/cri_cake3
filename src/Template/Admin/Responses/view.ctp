<div class="page-header">
	<h1>
		<?= $titleForLayout ?>
	</h1>
</div>

<p>
	<?= $this->Html->link(
		'<span class="glyphicon glyphicon-arrow-left"></span> Back to Survey Overview',
		[
			'prefix' => 'admin',
			'controller' => 'Surveys',
			'action' => 'view',
			$survey_id
		],
		[
			'class' => 'btn btn-default',
			'escape' => false
		]
	) ?>
</p>

<p>
	These are the currently known responses to
	<strong>
		<?= $community_name ?>'s
		community
		<?= $survey_type == 'official' ? 'leadership' : 'organization' ?>
	</strong>
	survey. Incomplete responses are excluded, and recent responses may have not been imported yet.
</p>

<?php if (empty($responses)): ?>
	<p class="alert alert-info">
		No responses have been imported yet.
	</p>
<?php else: ?>
	<?= $this->element('respondents'.DS.'admin_table') ?>
<?php endif; ?>

<h1>
	Update
</h1>
<?= $this->Form->create($survey) ?>
<?php
	if ($alignment_last_set) {
		$alignment_last_set_msg = '<br />Last modified: '.$alignment_last_set;
	} else {
		$alignment_last_set_msg = '';
	}
	echo $this->Form->input(
		'Survey.alignment',
		[
			'class' => 'form-control',
			'div' => [
				'class' => 'form-group'
			],
			'label' => 'Administrator-determined alignment (percent)'.$alignment_last_set_msg,
			'max' => 100,
			'min' => 0,
			'type' => 'number'
		]
	);
?>
<?= $this->Form->input(
	'Survey.alignment_passed',
	[
		'class' => 'form-control',
		'div' => [
			'class' => 'form-group'
		],
		'label' => 'Has this community passed its leadership alignment assessment?',
		'options' => [
			0 => 'Not determined',
			-1 => 'Failed',
			1 => 'Passed'
		],
		'type' => 'select'
	]
) ?>
<?= $this->Form->button(
	'Update',
	['class' => 'btn btn-primary']
) ?>
<?= $this->Form->end() ?>