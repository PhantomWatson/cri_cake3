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

	<?php $barChart->div('bar_chart_container'); ?>
	<div id="bar_chart_container"></div>
	<?php $this->GoogleCharts->createJsChart($barChart); ?>

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
				<?php foreach ($pwrTable as $broadCategory => $specificCategories): ?>
					<?php $firstRowInBc = true; ?>
					<?php foreach ($specificCategories as $specificCategory => $score): ?>
						<tr>
							<?php if ($firstRowInBc): ?>
								<th rowspan="<?= count($specificCategories) ?>">
									<?= $broadCategory ?>
								</th>
							<?php endif; ?>
							<td>
								<?= $specificCategory ?>
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
						<?php $firstRowInBc = false; ?>
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

	<?php $lineChart->div('line_chart_container'); ?>
	<div id="line_chart_container"></div>
	<?php $this->GoogleCharts->createJsChart($lineChart); ?>

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
						<?= $growthTable['earlier_year'] ?>
					</th>
					<th>
						<?= $growthTable['later_year'] ?>
					</th>
					<th>
						Change
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($growthTable['rows'] as $row): ?>
					<tr>
						<td>
							<?= $row['label'] ?>
						</td>
						<td>
							<?= number_format($row[$growthTable['earlier_year']]) ?>
						</td>
						<td>
							<?= number_format($row[$growthTable['later_year']]) ?>
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