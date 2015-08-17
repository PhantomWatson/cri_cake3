<div class="survey_overview">
	<?php if (! $is_open): ?>
		<p class="alert alert-info">
			Note: This survey is not yet ready to be administered.
		</p>
	<?php endif; ?>

	<?php if ($survey_url): ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					Invite
				</h3>
			</div>
			<div class="panel-body">
				<p>
					<?= $invited_respondent_count ?>
					community
					<?= __n("$survey_type has been sent a survey invitation", "{$survey_type}s have been sent survey invitations", $invited_respondent_count) ?>
				</p>
				<p>
					<?php if ($invited_respondent_count > 0): ?>
						<a href="#" class="btn btn-default invitations_toggler">
							View Invitations
						</a>
					<?php endif; ?>
					<?= $this->Html->link(
						'Send Invitations',
						[
							'prefix' => 'admin',
							'controller' => 'Surveys',
							'action' => 'invite',
							$survey_id
						],
						['class' => 'btn btn-default']
					) ?>
				</p>
				<?php if ($invited_respondent_count > 0): ?>
					<div class="invitations_list">
						<p>
							Invitations sent out for this survey:
						</p>
						<ul>
							<?php foreach ($invitations as $i => $email): ?>
								<li>
									<?= $email ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					Collect
				</h3>
			</div>
			<div class="panel-body">
				<p>
					<span class="last_import_time">
						<?php if ($responses_checked): ?>
							Responses were last imported
							<strong>
								<?= $this->Time->timeAgoInWords($responses_checked, ['end' => '+1 year']) ?>
							</strong>
						<?php else: ?>
							Responses have not been imported yet
						<?php endif; ?>
					</span>
				</p>
				<p>
					<?php if ($is_automatically_imported): ?>
						Responses are automatically imported from
						SurveyMonkey<?= $auto_import_frequency ? ' approximately '.$auto_import_frequency : '' ?>,
						but you can manually import them at any time.
					<?php else: ?>
						New responses to this survey are no longer being automatically imported from SurveyMonkey.
					<?php endif; ?>
				</p>
				<a href="#" class="btn btn-default import_button" data-survey-id="<?= $survey_id ?>">
					Import Responses
				</a>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					Review
				</h3>
			</div>
			<div class="panel-body">
				<p>
					<?php
						if ($percent_invited_responded < 33) {
							echo '<span class="text-danger">';
						} elseif ($percent_invited_responded < 66) {
							echo '<span class="text-warning">';
						} else {
							echo '<span class="text-success">';
						}
						echo $percent_invited_responded.'%</span>';
					?>
					of invited respondents have completed this survey
				</p>

				<?php if ($has_uninvited_unaddressed): ?>
					<p>
						<span class="text-warning">
							This survey has uninvited responses that need to be approved or dismissed.
						</span>
						<br />
						These responses will <strong>not</strong> be included in this community's alignment assessment unless if they are approved.
					</p>
				<?php endif; ?>

				<?php if (isset($has_new_responses) && $has_new_responses): ?>
					<p>
						<strong>
							New responses have been received
						</strong>
						since this community's alignment was last set by an administrator.
					</p>
				<?php endif; ?>

				<?php
					$button_class = (isset($has_new_responses) && $has_new_responses) ? 'primary' : 'default';
					echo $this->Html->link(
						'Review and Update Alignment',
						[
							'prefix' => 'admin',
							'controller' => 'Responses',
							'action' => 'view',
							$survey_id
						],
						['class' => 'btn btn-'.$button_class]
					);
				?>

				<?php if ($uninvited_respondent_count > 0): ?>
					<?= $this->Html->link(
						'Review / Approve Uninvited Responses',
						[
							'prefix' => 'admin',
							'controller' => 'Respondents',
							'action' => 'unapproved',
							$survey_id
						],
						['class' => 'btn btn-default']
					) ?>
				<?php endif; ?>
			</div>
		</div>
	<?php else: ?>
		<p class="alert alert-info">
			This community's <?= $survey_type ?>s survey URL has not yet been set. If the survey has been created,
			<?= $this->Html->link(
				'edit this community',
				[
					'prefix' => 'admin',
					'controller' => 'Communities',
					'edit',
					$community_id
				]
			) ?>
			and add its information.
		</p>
	<?php endif; ?>
</div>