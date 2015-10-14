<div id="communities_admin_index">
	<div class="page-header">
		<h1>
			<?= $titleForLayout ?>
		</h1>
	</div>

	<p>
		<?php foreach ($buttons as $group_label => $button_group): ?>
			<div class="btn-group">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					<?= $group_label ?>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" role="menu">
					<?php foreach ($button_group as $label => $filters): ?>
						<li>
							<?= $this->Html->link($label, compact('filters')) ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>

		<a href="#" class="btn btn-default" id="search_toggler">
			<span class="glyphicon glyphicon-search"></span>
			Search
		</a>

		<?= $this->Html->link(
			'<img src="/data_center/img/icons/document-excel-table.png" alt="Microsoft Excel (.xlsx)" /> Download',
			['action' => 'spreadsheet'],
			[
				'class' => 'btn btn-default',
				'escape' => false,
				'title' => 'Download this page as a Microsoft Excel (.xlsx) file'
			]
		) ?>

		<a href="#" class="btn btn-link" id="glossary_toggler">
			Icon Glossary
		</a>

		<?= $this->Html->link(
            'Add Community',
            [
                'prefix' => 'admin',
                'action' => 'add'
            ],
            ['class' => 'btn btn-success']
        ) ?>
	</p>

	<div class="alert alert-info" id="glossary">
		<table>
			<tbody>
				<tr>
					<td>
						<span class="glyphicon glyphicon-road fast_track" aria-hidden="true"></span> :
					</td>
					<td>
						Fast track
					</td>
				</tr>
				<tr>
					<td>
						<span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span> :
					</td>
					<td>
						Community has passed its alignment test
					</td>
				</tr>
				<tr>
					<td>
						<span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span> :
					</td>
					<td>
						Community has failed its alignment test
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<form class="form-inline" role="form" style="display: none;" id="admin_community_search_form">
		<input type="text" name="search" class="form-control" placeholder="Enter community name" />
		<button type="submit" class="btn btn-default">Search</button>
	</form>

    <?php if (isset($this->request->query['search'])): ?>
        <p class="alert alert-info" id="search_term">
            Search term: <strong><?= $this->request->query['search'] ?></strong>
            <?= $this->Html->link(
                'clear search',
                [
                    'prefix' => 'admin',
                    'controller' => 'communities',
                    'action' => 'index',
                    '?' => []
                ]
            ) ?>
        </p>
    <?php endif; ?>

	<?= $this->element('pagination') ?>

	<table class="table">
		<thead>
			<tr>
				<?php
					function getSortArrow($sortField, $query) {
						if (isset($query['sort']) && $query['sort'] == $sortField) {
							$direction = strtolower($query['direction']) == 'desc' ? 'up' : 'down';
							return '<span class="glyphicon glyphicon-arrow-'.$direction.'" aria-hidden="true"></span>';
						}
						return '';
					}
				?>
				<th>
					<?php
						$arrow = getSortArrow('Communities.name', $this->request->query);
						echo $this->Paginator->sort('Communities.name', 'Community'.$arrow, ['escape' => false]);
					?>
					/
					<?php
						$arrow = getSortArrow('Area.name', $this->request->query);
						echo $this->Paginator->sort('Area.name', 'Area'.$arrow, ['escape' => false]);
					?>
				</th>
				<th>
					Stage
				</th>
				<th>
					Officials Survey
				</th>
				<th>
					Organizations Survey
				</th>
				<th class="actions">
					Actions
				</th>
			</tr>
		</thead>
		<tbody>
			<?php if (empty($communities)): ?>
				<tr>
					<td colspan="4" class="no_results">
						No communities found matching the specified parameters
					</td>
				</tr>
			<?php endif; ?>

			<?php foreach ($communities as $community): ?>
				<tr>
					<td>
						<?= $community->name ?>
						<br />
						<span class="area_name">
							<?= $community->name ?>
						</span>
					</td>
					<td>
						<?= str_replace('.0', '', $community->score) ?>
						<?php if ($community->fast_track): ?>
							<span class="glyphicon glyphicon-road fast_track" aria-hidden="true" title="Fast Track"></span>
						<?php endif; ?>
					</td>

					<?php foreach (['OfficialSurvey', 'OrganizationSurvey'] as $survey_type): ?>
						<td>
							<?php if (isset($community->{$survey_type}['sm_id']) && $community->{$survey_type}['sm_id']): ?>
								<?= $this->Html->link(
									'Overview',
									[
										'prefix' => 'admin',
										'controller' => 'Surveys',
										'action' => 'view',
										$community->{$survey_type}['id']
									]
								) ?>
								<br />
								<?php if ($community->{$survey_type}['alignment'] === null): ?>
									Alignment: Not set
								<?php else: ?>
									Alignment: <?php echo $community->{$survey_type}['alignment']; ?>%

									<?php if ($community->{$survey_type}['alignment_passed'] == -1): ?>
										<span class="glyphicon glyphicon-remove-sign" aria-hidden="true" title="Failed to pass"></span>
									<?php elseif ($community->{$survey_type}['alignment_passed'] == 1): ?>
										<span class="glyphicon glyphicon-ok-sign" aria-hidden="true" title="Passed"></span>
									<?php endif; ?>

								<?php endif; ?>

								<?php if (isset($community->{$survey_type}['respondents_last_modified_date']) && $community->{$survey_type}['respondents_last_modified_date']): ?>
									<br /> Last response:
									<?php
										$timestamp = strtotime($community->{$survey_type}['respondents_last_modified_date']);
										echo date('n/j/Y', $timestamp);
									?>
								<?php endif; ?>
							<?php else: ?>
								<?= $this->Html->link(
									'Not set up yet',
									[
										'prefix' => 'admin',
										'controller' => 'communities',
										'action' => 'edit',
										$community->id
									]
								) ?>
							<?php endif; ?>
						</td>
					<?php endforeach; ?>

					<td class="actions btn-group">
						<div class="btn-group">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								Actions <span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu">
								<li>
							    	<?= $this->Html->link(
										'Progress',
										[
											'prefix' => 'admin',
											'action' => 'progress',
											$community->id
										]
									) ?>
								</li>
								<li>
						    		<?= $this->Html->link(
										'Clients ('.count($community->client).')',
										[
											'prefix' => 'admin',
											'action' => 'clients',
											$community->id
										]
									) ?>
						    	</li>
						    	<?php if (! empty($community->client)): ?>
							    	<li>
							    		<?= $this->Html->link(
											'Client Home',
											[
												'prefix' => 'admin',
												'action' => 'clienthome',
												$community->id
											]
										) ?>
							    	</li>
							    <?php endif; ?>
								<li>
							    	<?= $this->Html->link(
										'Performance Charts',
										[
											'prefix' => false,
											'action' => 'view',
											$community->id
										]
									) ?>
								</li>
						    	<li>
						    		<?= $this->Html->link(
										'Edit Community',
										[
											'prefix' => 'admin',
											'action' => 'edit',
											$community->id
										]
									) ?>
						    	</li>
						    	<li>
						    		<?= $this->Form->postLink(
										'Delete Community',
										[
											'prefix' => 'admin',
											'action' => 'delete',
											$community->id
										],
										['confirm' => "Are you sure you want to delete {$community->name}? This cannot be undone."]
									); ?>
								</li>
							</ul>
						</div>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?= $this->element('pagination') ?>
</div>
<?php
	$this->Html->script('admin', ['block' => 'scriptBottom']);
	echo $this->element('DataCenter.jquery_ui');
?>

<?php $this->append('buffered'); ?>
	adminCommunitiesIndex.init();
<?php $this->end();