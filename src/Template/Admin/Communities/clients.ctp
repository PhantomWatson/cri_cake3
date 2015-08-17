<div class="page-header">
	<h1>
		<?= $title_for_layout ?>
	</h1>
</div>

<p>
	<?= $this->Html->link(
		'<span class="glyphicon glyphicon-arrow-left"></span> Back to Communities',
		[
			'prefix' => 'admin',
			'controller' => 'Communities',
			'action' => 'index'
		],
		[
			'class' => 'btn btn-default',
			'escape' => false
		]
	) ?>
</p>

<?php if (empty($clients)): ?>
	<p class="alert alert-info">
		This community does not have any client accounts associated with it.
	</p>
<?php else: ?>
	<table class="table">
		<thead>
			<tr>
				<th>
					Name
				</th>
				<th>
					Email
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($clients as $client): ?>
				<tr>
					<td>
						<?= $client['name'] ?>
					</td>
					<td>
						<a href="mailto:<?= $client['email'] ?>">
							<?= $client['email'] ?>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>