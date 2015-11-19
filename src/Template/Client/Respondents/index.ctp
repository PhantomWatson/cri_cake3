<div class="page-header">
	<h1>
		<?= $titleForLayout ?>
	</h1>
</div>

<p>
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
</p>

<?php if (empty($respondents)): ?>
	<p class="alert alert-info">
		No invitations have been sent out for this survey.
	</p>
<?php else: ?>

	<?= $this->element('pagination') ?>

	<table class="table respondents">
		<thead>
			<tr>
				<th>
					<?= $this->Paginator->sort('email', 'Respondent') ?>
				</th>

				<?php if ($surveyType == 'official'): ?>
					<th>
						<?= $this->Paginator->sort('approved', 'Approved') ?>
					</th>
				<?php endif; ?>

				<th>
					Completed Survey
				</th>
				<th>
					Completion Date
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($respondents as $respondent): ?>
				<tr>
					<td>
						<?= $respondent->name ? $respondent->name : '(No name)' ?>
						<br />
						<span class="email">
						    <?= $respondent->email ? $respondent->email : '(No email)' ?>
					    </span>
					</td>

					<?php if ($surveyType == 'official'): ?>
						<td class="boolean_icon">
							<span class="glyphicon glyphicon-<?= empty($respondent->approved) ? 'remove' : 'ok' ?>"></span>
						</td>
					<?php endif; ?>

					<td class="boolean_icon">
						<span class="glyphicon glyphicon-<?= empty($respondent->responses) ? 'remove' : 'ok' ?>"></span>
					</td>
					<td>
						<?php
							if (isset($respondent->responses[0]['response_date']) && $respondent->responses[0]['response_date'] != null) {
								$timestamp = strtotime($respondent->responses[0]['response_date']);
								echo date('F j, Y', $timestamp);
							}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?= $this->element('pagination') ?>

<?php endif; ?>