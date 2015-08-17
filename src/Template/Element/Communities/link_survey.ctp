<div class="link_survey form-group">
	<strong>
		Community <?= $type == 'Official' ? 'Leadership' : 'Organizations' ?> Survey:
	</strong>

	<div class="link_label">
		<?php if (isset($this->request->data["{$type}Survey"]['sm_url']) && $this->request->data["{$type}Survey"]['sm_url']): ?>
			<span class="label label-success">
				Linked
			</span>
		<?php else: ?>
			<span class="label label-danger">
				Not linked
			</span>
		<?php endif; ?>
	</div>

	<div class="link_status">
		<?php if (isset($this->request->data["{$type}Survey"]['sm_url']) && $this->request->data["{$type}Survey"]['sm_url']): ?>
			<a href="<?= $this->request->data["{$type}Survey"]['sm_url'] ?>">
				<?= $this->request->data["{$type}Survey"]['sm_url'] ?>
			</a>
		<?php endif; ?>
	</div>

	<ul class="actions">
		<li>
			<a href="#" class="lookup btn btn-default btn-sm">
				Select survey
			</a>
		</li>
		<li>
			<a href="#" class="show_details btn btn-default btn-sm">
				Show details
			</a>
		</li>
		<?php if (isset($this->request->data["{$type}Survey"]['id'])): ?>
			<li>
				<?= $this->Html->link(
					'Go to survey overview <span class="glyphicon glyphicon-share-alt"></span>',
					[
						'prefix' => 'admin',
						'controller' => 'Surveys',
						'action' => 'view',
						$this->request->data["{$type}Survey"]['id']
					],
					[
						'class' => 'btn btn-default btn-sm',
						'escape' => false
					],
					'Go to survey overview page without updating community?'
				) ?>
			</li>
		<?php endif; ?>
	</ul>

	<div class="lookup_results well"></div>

	<div class="details well">
		<?php
			echo $this->Form->hidden("{$type}Survey.id");
			echo $this->Form->hidden("{$type}Survey.type");
			echo $this->Form->hidden("{$type}Survey.community_id");
		?>

		<table class="table">
			<?php
				echo $this->Form->input(
					"{$type}Survey.sm_id",
					[
						'class' => 'form-control survey_sm_id',
						'div' => false,
						'label' => 'SurveyMonkey Survey ID',
						'type' => 'number',

						'before' => '<tr><td>',
						'between' => '</td><td>',
						'after' => '</td></tr>'
					]
				);
				echo $this->Form->input(
					"{$type}Survey.sm_url",
					[
						'class' => 'form-control survey_url',
						'div' => false,
						'label' => 'SurveyMonkey Survey URL',

						'before' => '<tr><td>',
						'between' => '</td><td>',
						'after' => '</td></tr>'
					]
				);
				foreach ($qna_id_fields as $qna_id_field) {
					$label = Inflector::humanize($qna_id_field);
					$label = str_ireplace('qid', 'Question ID', $label);
					$label = str_ireplace('aid', 'Answer ID', $label);
					$label = str_ireplace('pwrrr', 'PWR<sup>3</sup>&trade;', $label);
					echo $this->Form->input(
						"{$type}Survey.{$qna_id_field}",
						[
							'class' => 'form-control',
							'data-fieldname' => $qna_id_field,
							'div' => false,
							'label' => $label,
							'type' => 'number',

							'before' => '<tr><td>',
							'between' => '</td><td>',
							'after' => '</td></tr>'
						]
					);
				}
			?>
		</table>
	</div>
</div>