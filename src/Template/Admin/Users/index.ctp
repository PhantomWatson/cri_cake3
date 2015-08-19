<div id="users_index">
	<div class="page-header">
		<h1>
			<?= $titleForLayout ?>
		</h1>
	</div>

	<p>
		<?= $this->Html->link(
			'Add User',
			[
				'prefix' => 'admin',
				'action' => 'add'
			],
			['class' => 'btn btn-success']
		) ?>
	</p>

	<p>
		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
				<?php
					if (isset($this->request->query['filter'])) {
						$currentFilter = $this->request->query['filter'];
					} else {
						$currentFilter = 'all';
					}
				?>
				Type:
				<strong>
					<?= $buttons[$currentFilter] ?>
				</strong>
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu" role="menu">
				<?php foreach ($buttons as $filter => $label): ?>
					<?php if ($filter == $currentFilter) continue; ?>
					<li>
						<?= $this->Html->link(
							$label,
							['?' => compact('filter')]
						) ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</p>

	<table class="table">
		<thead>
			<tr>
				<th>
					<?= $this->Paginator->sort('name', 'User') ?>
				</th>
				<th>
					<?= $this->Paginator->sort('role') ?>
				</th>
				<th>
					<?= $this->Paginator->sort('created', 'Added') ?>
				</th>
				<th class="actions">
					Actions
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($users as $user): ?>
				<tr>
					<td>
						<?= h($user['name']) ?>
						<br />
						<a href="mailto:<?= h($user['email']) ?>">
							<?= h($user['email']) ?>
						</a>
					</td>
					<td>
						<?= ucwords($user['role']) ?>
					</td>
					<td>
						<?php
							$timestamp = strtotime($user['created']);
							echo date('F j, Y', $timestamp);
						?>
					</td>
					<td class="actions btn-group">
						<?= $this->Html->link(
							'Edit',
							[
								'prefix' => 'admin',
								'action' => 'edit',
								'id' => $user['id']
							],
							['class' => 'btn btn-default']
						) ?>
						<?= $this->Form->postLink(
							'Delete',
							[
								'prefix' => 'admin',
								'action' => 'delete',
								'id' => $user['id']
							],
							['class' => 'btn btn-default'],
							"Are you sure you want to delete {$user['name']}'s account?"
						) ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?= $this->element('pagination') ?>
</div>