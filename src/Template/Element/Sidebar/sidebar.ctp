<?php use Cake\Core\Configure; ?>

<a href="/" id="sidebar_logo">
	<img src="/img/cri_logo.png" alt="Community Readiness Initiative" />
</a>

<?php if ($authUser): ?>
	<nav class="logged_in">
		<h2 class="logged_in">
			Logged in as
			<?php if (Configure::read('debug')): ?>
				<strong>
					<?= $authUser['role'] ?>
				</strong>
			<?php endif; ?>
			<?= $authUser['name'] ?>
		</h2>

		<?php if ($authUser['role'] == 'admin'): ?>

			<?= $this->element('Sidebar/admin') ?>

		<?php elseif ($authUser['role'] == 'client'): ?>

			<?= $this->element('Sidebar/client') ?>

		<?php endif; ?>
	</nav>
<?php endif; ?>

<nav class="public_nav">
	<ul>
		<li class="link">
			<?= $this->Html->link(
				'CRI Home',
				'/'
			) ?>
		</li>
		<?php if (! empty($accessible_communities)): ?>
			<li>
				<p>
					County Performance
				</p>
				<form method="get" id="community_select" action="/communities/view">
					<select name="cid" required>
						<option value="">
							Select a community...
						</option>
						<?php foreach ($accessible_communities as $community_id => $community_name): ?>
							<option value="<?= $community_id ?>">
								<?= $community_name ?>
							</option>
						<?php endforeach; ?>
					</select>
					<input type="submit" value="" />
				</form>
			</li>
		<?php endif; ?>
		<li class="link">
			<?= $this->Html->link(
				'Community Progress',
				[
					'prefix' => false,
					'controller' => 'Communities',
					'action' => 'index'
				]
			) ?>
		</li>
		<li class="link">
			<?= $this->Html->link(
				'FAQs for Communities',
				[
					'prefix' => false,
					'controller' => 'Pages',
					'action' => 'faqCommunity'
				]
			) ?>
		</li>
		<li class="link">
			<?= $this->Html->link(
				'FAQs for Consultants',
				[
					'prefix' => false,
					'controller' => 'Pages',
					'action' => 'faqConsultants'
				]
			) ?>
		</li>
		<li class="link">
			<?= $this->Html->link(
				'CRI Fast Track',
				[
					'prefix' => false,
					'controller' => 'Pages',
					'action' => 'fasttrack'
				]
			) ?>
		</li>
		<li class="link">
			<?= $this->Html->link(
				'Credits and Sources',
				[
					'prefix' => false,
					'controller' => 'Pages',
					'action' => 'credits'
				]
			) ?>
		</li>
		<?php if (! $authUser): ?>
			<li class="link">
				<?= $this->Html->link(
					'Login',
					[
						'prefix' => false,
						'controller' => 'Users',
						'action' => 'login'
					]
				) ?>
			</li>
		<?php endif; ?>
	</ul>
</nav>

<?php $this->append('buffered'); ?>
    setupSidebar();
<?php $this->end(); ?>