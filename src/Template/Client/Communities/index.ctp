<div class="page-header">
	<h1>
		<?= $title_for_layout ?>
	</h1>
</div>

<?php
	function glyphicon($bool) {
		$class = $bool ? 'ok' : 'remove';
		return '<span class="glyphicon glyphicon-'.$class.'"></span>';
	}
?>

<div id="client_home">
	<table>
		<tbody <?php if ($score < 2) echo 'class="current"'; ?>>
			<tr>
				<th colspan="3">
					Step One: Sign Up
				</th>
			</tr>
			<tr>
				<td>
					<?= glyphicon($criteria[1]['survey_purchased'][1]) ?>
				</td>
				<td>
					<?= $criteria[1]['survey_purchased'][0] ?>
				</td>
				<td>
					<?php if (! $criteria[1]['survey_purchased'][1]): ?>
						<a href="<?= $purchase_urls[1] ?>" class="btn btn-primary">
							Purchase Now
						</a>
					<?php endif; ?>
				</td>
			</tr>

			<?php if (isset($criteria[1]['survey_created'])): ?>
				<?php $leadership_survey_created = $criteria[1]['survey_created'][1]; ?>
				<tr>
					<td>
						<?= glyphicon($criteria[1]['survey_created'][1]) ?>
					</td>
					<td>
						<?= $criteria[1]['survey_created'][0] ?>
						<?php if (! $criteria[1]['survey_created'][1] && $score < 2): ?>
							<p class="alert alert-info">
								Your community's survey is currently being prepared. Please check back later for updates.
							</p>
						<?php endif; ?>
					</td>
					<td>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>

		<tbody <?php if ($score >= 2 && $score < 3) echo 'class="current"'; ?>>
			<tr>
				<th colspan="3">
					Step Two: Leadership Alignment Assessment
				</th>
			</tr>

			<?php if (isset($criteria[2]['survey_created'])): ?>
				<?php $leadership_survey_created = $criteria[2]['survey_created'][1] ?>
				<tr>
					<td>
						<?= glyphicon($criteria[2]['survey_created'][1]) ?>
					</td>
					<td>
						<?= $criteria[2]['survey_created'][0] ?>
						<?php if (! $criteria[2]['survey_created'][1] && $score >= 2 && $score < 3): ?>
							<p class="alert alert-info">
								Your community's survey is currently being prepared. Please check back later for updates.
							</p>
						<?php endif; ?>
					</td>
					<td>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<td>
					<?= glyphicon($criteria[2]['invitations_sent'][1]) ?>
				</td>
				<td>
					<?= $criteria[2]['invitations_sent'][0] ?>
				</td>
				<td>
					<?php if ($leadership_survey_created): ?>
						<?= $this->Html->link(
							'Send '.($criteria[2]['invitations_sent'][1] ? 'More ' : '').'Invitations',
							[
								'prefix' => 'client',
								'controller' => 'Surveys',
								'action' => 'invite',
								'officials'
							],
							['class' => 'btn btn-'.($criteria[2]['invitations_sent'][1] ? 'default' : 'primary')]
						) ?>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?= glyphicon($criteria[2]['responses_received'][1]) ?>
				</td>
				<td>
					<p>
						<?= $criteria[2]['responses_received'][0] ?>
						<a href="#" class="importing_note_toggler">
							<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
						</a>
					</p>
					<p class="importing_note" style="display: none;">
						<?php if ($score < 3): ?>
							Responses are automatically imported from
							SurveyMonkey<?= $auto_import_frequency ? ' approximately '.$auto_import_frequency : '' ?>,
							but you can manually import them at any time.
						<?php else: ?>
							New responses to this survey are no longer being automatically imported from SurveyMonkey.
						<?php endif; ?>
					</p>
					<?php if ($official_responses_checked): ?>
						<p class="last_import alert alert-info">
							New responses were last checked for
							<?= $this->Time->timeAgoInWords($official_responses_checked, ['end' => '+1 year']) ?>
						</p>
					<?php endif; ?>
				</td>
				<td>
					<?php if ($leadership_survey_created): ?>
						<a href="#" class="btn btn-default import_button" data-survey-id="<?= $official_survey_id ?>">
							Import Responses
						</a>
						<br />
						<?php if ($criteria[2]['responses_received'][1]): ?>
							<?= $this->Html->link(
								'Review Responses',
								[
									'prefix' => 'client',
									'controller' => 'Respondents',
									'action' => 'index',
									'official'
								],
								['class' => 'btn btn-default']
							) ?>
						<?php endif; ?>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?= glyphicon($criteria[2]['response_threshhold_reached'][1]) ?>
				</td>
				<td>
					<?= $criteria[2]['response_threshhold_reached'][0] ?>
				</td>
				<td>
				</td>
			</tr>

			<?php if (isset($criteria[2]['unapproved_addressed'])): ?>
				<tr>
					<td>
						<?= glyphicon($criteria[2]['unapproved_addressed'][1]) ?>
					</td>
					<td>
						<?= $criteria[2]['unapproved_addressed'][0] ?>
					</td>
					<td>
						<?= $this->Html->link(
							'Approve / Dismiss',
							[
								'prefix' => 'client',
								'controller' => 'Respondents',
								'action' => 'unapproved',
								'official'
							],
							['class' => 'btn btn-'.($criteria[2]['unapproved_addressed'][1] ? 'default' : 'primary')]
						) ?>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<td>
					<?= glyphicon($criteria[2]['alignment_calculated'][1]) ?>
				</td>
				<td>
					<?= $criteria[2]['alignment_calculated'][0] ?>
				</td>
				<td>
				</td>
			</tr>

			<?php if (isset($criteria[2]['summit_purchased'])): ?>
				<tr>
					<td>
						<?= glyphicon($criteria[2]['summit_purchased'][1]) ?>
					</td>
					<td>
						<?= $criteria[2]['summit_purchased'][0] ?>
					</td>
					<td>
						<?php if (! $criteria[2]['summit_purchased'][1]): ?>
							<a href="<?= $purchase_urls[2]; ?>" class="btn btn-primary">
								Purchase Now
							</a>
						<?php endif; ?>
					</td>
				</tr>

				<?php if ($step_2_alignment): ?>
					<tr>
						<td>
							<?= glyphicon($step_2_alignment[1]) ?>
						</td>
						<td>
							<?= $step_2_alignment[0] ?>
						</td>
						<td>
						</td>
					</tr>
				<?php endif; ?>
			<?php else: ?>
				<?php if ($step_2_alignment): ?>
					<tr>
						<td>
							<?= glyphicon($step_2_alignment[1]) ?>
						</td>
						<td>
							<?= $step_2_alignment[0] ?>
						</td>
						<td>
						</td>
					</tr>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ($step_2_survey_purchased): ?>
				<tr>
					<td>
						<?= glyphicon($step_2_survey_purchased[1]) ?>
					</td>
					<td>
						<?= $step_2_survey_purchased[0] ?>
					</td>
					<td>
						<?php if (! $step_2_survey_purchased[1]): ?>
							<a href="<?= $purchase_urls[3]; ?>" class="btn btn-primary">
								Purchase Now
							</a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>

		<?php if ($fast_track): ?>
			<tbody>
				<tr>
					<th colspan="3">
						Step Three and Four skipped in Fast Track
					</th>
				</tr>
			</tbody>
		<?php else: ?>
			<tbody <?php if ($score >= 3 && $score < 4) echo 'class="current"'; ?>>
				<tr>
					<th colspan="3">
						Step Three: Community Organizations Alignment Assessment
					</th>
				</tr>
				<tr>
					<td>
						<?= glyphicon($criteria[3]['survey_created'][1]) ?>
					</td>
					<td>
						<?= $criteria[3]['survey_created'][0] ?>
						<?php if (! $criteria[3]['survey_created'][1] && $score >= 3 && $score < 4): ?>
							<p class="alert alert-info">
								Your community's survey is currently being prepared. Please check back later for updates.
							</p>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($organization_survey_open): ?>
						    <?= $this->Html->link(
								'Send Invitations',
								[
									'prefix' => 'client',
									'controller' => 'Surveys',
									'action' => 'invite',
									'organizations'
								],
								['class' => 'btn btn-default']
							) ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td>
						<?= glyphicon($criteria[3]['responses_received'][1]) ?>
					</td>
					<td>
						<p>
							<?= $criteria[3]['responses_received'][0] ?>
							<a href="#" class="importing_note_toggler">
								<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
							</a>
						</p>
						<p class="importing_note" style="display: none;">
							<?php if ($score < 4): ?>
								Responses are automatically imported from
								SurveyMonkey<?= $auto_import_frequency ? ' approximately '.$auto_import_frequency : '' ?>,
								but you can manually import them at any time.
							<?php else: ?>
								New responses to this survey are no longer being automatically imported from SurveyMonkey.
							<?php endif; ?>
						</p>
					</td>
					<td>
						<?php if ($criteria[3]['survey_created'][1]): ?>
							<a href="#" class="btn btn-default import_button" data-survey-id="<?= $organization_survey_id ?>">
								Import Responses
							</a>
							<br />
							<?php if ($criteria[3]['responses_received'][1]): ?>
								<?= $this->Html->link(
									'Review Responses',
									[
										'prefix' => 'client',
										'controller' => 'Respondents',
										'action' => 'index',
										'organization'
									],
									['class' => 'btn btn-default']
								) ?>
							<?php endif; ?>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td>
						<?= glyphicon($criteria[3]['alignment_calculated'][1]) ?>
					</td>
					<td>
						<?= $criteria[3]['alignment_calculated'][0] ?>
					</td>
					<td>
					</td>
				</tr>

				<?php if (isset($criteria[3]['summit_purchased'])): ?>
					<tr>
						<td>
							<?= glyphicon($criteria[3]['summit_purchased'][1]) ?>
						</td>
						<td>
							<?= $criteria[3]['summit_purchased'][0] ?>
						</td>
						<td>
							<?php if (! $criteria[3]['summit_purchased'][1]): ?>
								<a href="<?= $purchase_urls[4] ?>" class="btn btn-primary">
									Purchase Now
								</a>
							<?php endif; ?>
						</td>
					</tr>

					<?php if ($step_3_alignment): ?>
						<tr>
							<td>
								<?= glyphicon($step_3_alignment[1]) ?>
							</td>
							<td>
								<?= $step_3_alignment[0] ?>
							</td>
							<td>
							</td>
						</tr>
					<?php endif; ?>
				<?php else: ?>
					<?php if ($step_3_alignment): ?>
						<tr>
							<td>
								<?= glyphicon($step_3_alignment[1]) ?>
							</td>
							<td>
								<?= $step_3_alignment[0] ?>
							</td>
							<td>
							</td>
						</tr>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ($step_3_policy_dev_purchased): ?>
					<tr>
						<td>
							<?= glyphicon($step_3_policy_dev_purchased[1]) ?>
						</td>
						<td>
							<?= $step_3_policy_dev_purchased[0] ?>
						</td>
						<td>
							<?php if (! $step_3_policy_dev_purchased[1]): ?>
								<a href="<?= $purchase_urls[5] ?>" class="btn btn-primary">
									Purchase Now
								</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>

			<tbody <?php if ($score >= 4 && $score < 5) echo 'class="current"'; ?>>
				<tr>
					<th colspan="3">
						Step Four: Review of Findings
					</th>
				</tr>
				<tr>
					<td>
						<?= glyphicon($criteria[4]['meeting_scheduled'][1]) ?>
					</td>
					<td>
						<?= $criteria[4]['meeting_scheduled'][0] ?>
					</td>
					<td>
					</td>
				</tr>

				<?php if (isset($criteria[4]['meeting_held'])): ?>
					<tr>
						<td>
							<?= glyphicon($criteria[4]['meeting_held'][1]) ?>
						</td>
						<td>
							<?= $criteria[4]['meeting_held'][0] ?>
						</td>
						<td>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		<?php endif; ?>

		<tbody <?php if ($score == 5) echo 'class="current"'; ?>>
			<tr>
				<th colspan="3">
					Step Five: Conclusion
				</th>
			</tr>
		</tbody>
	</table>
</div>

<?php $this->Html->script('client', ['block' => 'scriptBottom']); ?>
<?php $this->append('buffered'); ?>
	clientHome.init();
<?php $this->end();