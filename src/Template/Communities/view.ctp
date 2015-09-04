<div class="page-header">
	<h1>
		<?= $titleForLayout ?>
	</h1>
</div>

<section class="community_data_section">
	<h2>
		PWR<sup>3</sup> &trade; Performance
	</h2>
	<p>
		The PWR<sup>3</sup> &trade; (Power Cubed) name is an acronym representing the five economic activities
		and elements that are broadly applicable to any regional economy and business sector:
		<strong>P</strong>roduction,
		<strong>W</strong>holesale,
		<strong>R</strong>etail,
		<strong>R</strong>esidential, and
		<strong>R</strong>ecreation.
		This information will help you identify the unique combination of strengths in your region and
		determine how to leverage those strengths for effective community growth.
	</p>

	<?php $bar_chart->div('bar_chart_container'); ?>
	<div id="bar_chart_container"></div>
	<?php $this->GoogleCharts->createJsChart($bar_chart); ?>

	<p class="footnote">
		Data Source: Author calculations and Bureau of Economic Analysis
	</p>

	<a href="#" id="pwr_table_toggler" class="btn btn-primary">
		Show table
	</a>

	<?php $this->append('buffered'); ?>
		$('#pwr_table_toggler').click(function (event) {
			event.preventDefault();
			$('#pwr_table').slideToggle();
		});
	<?php $this->end(); ?>

	<div id="pwr_table">
		<table class="table pwr">
			<thead>
				<tr>
					<th colspan="2">
						Category
					</th>
					<th>
						Score
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($pwr_table as $broad_category => $specific_categories): ?>
					<?php $first_row_in_bc = true; ?>
					<?php foreach ($specific_categories as $specific_category => $score): ?>
						<tr>
							<?php if ($first_row_in_bc): ?>
								<th rowspan="<?= count($specific_categories) ?>">
									<?= $broad_category ?>
								</th>
							<?php endif; ?>
							<td>
								<?= $specific_category ?>
							</td>
							<td>
								<?php if ($score > 1): ?>
									<span class="above_average">
									    <?= $score ?>
								    </span>
								<?php elseif ($score < 1): ?>
									<span class="below_average">
									    <?= $score ?>
								    </span>
								<?php else: ?>
									<?= $score ?>
								<?php endif; ?>
							</td>
						</tr>
						<?php $first_row_in_bc = false; ?>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>

<section class="community_data_section">
	<h2>
		Exportable and Non-Exportable Sector Employment, 1969-2011
	</h2>
	<p>
		This figure shows the balance between employment in exportable and non-exportable sectors.
		Note the changes around each recessionary period.
	</p>

	<?php $line_chart->div('line_chart_container'); ?>
	<div id="line_chart_container"></div>
	<?php $this->GoogleCharts->createJsChart($line_chart); ?>

	<p>
		<strong>Note: </strong> Recessions were declared by NBER during the following periods:
	</p>
	<ul>
		<li>Nov. 1973 - March 1975,</li>
		<li>Jan. 1980 - July 1980,</li>
		<li>July 1981 - Nov. 1982,</li>
		<li>July 1990 - March 1991,</li>
		<li>March 2001 - Nov. 2001,</li>
		<li>Dec. 2007 - June 2009</li>
	</ul>

	<p class="footnote">
		Data Source: Author calculations and Bureau of Economic Analysis
	</p>
</section>

<section class="community_data_section">
	<h2>
		Change in Employment, 2006 and 2011
	</h2>
	<p>
		This table shows area employment before and after the 2007-2009 recession.
	</p>

	<div id="employment_growth_table">
		<table>
			<thead>
				<tr>
					<th>
						Sectors
					</th>
					<th>
						<?= $growth_table['earlier_year'] ?>
					</th>
					<th>
						<?= $growth_table['later_year'] ?>
					</th>
					<th>
						Change
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($growth_table['rows'] as $row): ?>
					<tr>
						<td>
							<?= $row['label'] ?>
						</td>
						<td>
							<?= number_format($row[$growth_table['earlier_year']]) ?>
						</td>
						<td>
							<?= number_format($row[$growth_table['later_year']]) ?>
						</td>
						<td>
							<?= $row['change'] ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<p class="footnote">
		Data Source: Author calculations and Bureau of Economic Analysis
	</p>
</section>