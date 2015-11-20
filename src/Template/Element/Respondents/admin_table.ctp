<?php
    use Cake\Validation\Validation;
?>

<div id="admin_responses_view">
	<table class="table">
		<thead class="actual">
			<td colspan="3">
				<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
				<?= $area['Area']['name'] ?>
				<br />
				(Actual rankings)
			</td>
			<?php foreach ($sectors as $sector): ?>
				<td>
					<?= $area['Area']["{$sector}_rank"] ?>
				</td>
			<?php endforeach; ?>
			<td>
			</td>
			<td>
			</td>
		</thead>
		<thead>
			<tr>
				<th>
				</th>
				<?php
					function getSortArrow($sortField, $params) {
						if (isset($params['named']['sort']) && $params['named']['sort'] == $sortField) {
							$direction = strtolower($params['named']['direction']) == 'desc' ? 'up' : 'down';
							return '<span class="glyphicon glyphicon-arrow-'.$direction.'" aria-hidden="true"></span>';
						}
						return '';
					}
				?>
				<th>
					<?php
						$arrow = getSortArrow('response_date', $this->request->params);
						echo $this->Paginator->sort('response_date', 'Date'.$arrow, ['escape' => false]);
					?>
				</th>
				<th>
					Revisions
				</th>
				<?php foreach ($sectors as $sector): ?>
					<th>
						<?php
							$arrow = getSortArrow($sector.'_rank', $this->request->params);
							echo $this->Paginator->sort($sector.'_rank', ucwords($sector).$arrow, ['escape' => false]);
						?>
					</th>
				<?php endforeach; ?>
				<th>
					<?php
						$arrow = getSortArrow('alignment', $this->request->params);
						echo $this->Paginator->sort('alignment', 'Alignment'.$arrow, ['escape' => false]);
					?>
				</th>
				<th>
					<?php
						$arrow = getSortArrow('Respondent.approved', $this->request->params);
						echo $this->Paginator->sort('Respondent.approved', 'Approved'.$arrow, ['escape' => false]);
					?>
				</th>
				<th class="selected">
					Selected
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($responses as $response): ?>
				<tr>
					<td>
						<div class="respondent_popup">
							<?php if ($response['Respondent']['name']): ?>
								<?= $response['Respondent']['name'] ?>
							<?php else: ?>
								<span class="no_name">
									No name provided
								</span>
							<?php endif; ?>
							<br />
							<?php if (Validation::email($response['Respondent']['email'])): ?>
								<a href="mailto:<?= $response['Respondent']['email'] ?>">
									<?= $response['Respondent']['email'] ?>
								</a>
							<?php else: ?>
								<?= $response['Respondent']['email'] ?>
							<?php endif; ?>
						</div>
						<a href="#" class="respondent_popup_handle" title="Show respondent info">
							<span class="glyphicon glyphicon-info-sign"></span>
						</a>
					</td>

					<td>
						<?php
							$timestamp = strtotime($response['Response']['response_date']);
							echo date('n/j/y', $timestamp);
						?>
					</td>

					<td>
						<?= $response['revision_count'] ?>
					</td>

					<?php foreach ($sectors as $sector): ?>
						<?php
							$respondentRank = $response['Response'][$sector.'_rank'];
							$actualRank = $area['Area']["{$sector}_rank"];
							$difference = abs($respondentRank - $actualRank);
							if ($difference > 2) {
								$class = 'incorrect';
							} elseif ($difference > 0) {
								$class = 'near';
							} else {
								$class = 'correct';
							}
						?>
						<td class="<?= $class ?>">
							<?= $respondentRank ?>
						</td>
					<?php endforeach; ?>

					<td>
						<?= $response['Response']['alignment'] ?>%
					</td>

					<td>
						<?php if ($response['Respondent']['approved']): ?>
							<span class="glyphicon glyphicon-ok"></span>
						<?php else: ?>
							<span class="glyphicon glyphicon-remove"></span>
						<?php endif; ?>
					</td>

					<td class="selected">
						<?php $checked = ($response['Respondent']['approved']) ? 'checked' : ''; ?>
						<input type="checkbox" class="custom_alignment_calc" data-alignment="<?= $response['Response']['alignment'] ?>" <?= $checked ?> />
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="8">
					Calculated total alignment
					<br />
					<a href="#" id="toggle_custom_calc">
						Edit what responses are used in this calculation
					</a>
				</td>
				<td>
					<?= $totalAlignment ?>%
				</td>
				<td>
				</td>
				<td class="selected">
					<?= $totalAlignment ?>%
				</td>
			</tr>
		</tfoot>
	</table>
</div>
<?php $this->Html->script('admin', ['inline' => 'scriptBottom']); ?>
<?php $this->append('buffered'); ?>
	adminViewResponses.init();
<?php $this->end(); ?>