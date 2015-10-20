<div class="page-header">
	<h1>
		<?= $titleForLayout ?>
	</h1>
</div>

<div id="communities_index">
	<?php if (empty($communities)): ?>
		<p class="alert alert-info">
			Sorry, in this early stage of the Community Readiness Initiative, no information about community progress is available yet.
			Please check back later.
		</p>
	<?php else: ?>
		<table class="table">
			<thead>
				<tr>
					<th>
						Community
					</th>
					<?php for ($n = 1; $n <= 5; $n++): ?>
						<th>
							Step <?= $n ?>
						</th>
					<?php endfor; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($communities as $community): ?>
				    <tr <?= ($community->score == 0 ? 'class="no_score"' : '') ?>>
						<th>
							<?= $community->name ?>
						</th>
						<?php for ($n = 1; $n <= 5; $n++): ?>
							<td>
								<?php if ($community->score >= $n): ?>
									<span class="glyphicon glyphicon-ok"></span>
								<?php endif; ?>
							</td>
						<?php endfor; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>