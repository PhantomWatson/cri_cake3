<?php /*
	<h2> deliberately omitted so the admin block can insert a <select> between it and <ul>
*/ ?>
<ul>
	<li class="link client_home">
		<?= $this->Html->link(
			'Client Home',
			[
				'prefix' => 'client',
				'controller' => 'Communities',
				'action' => 'index'
			]
		) ?>
	</li>
	<?php if ($userRole == 'client'): ?>
		<li class="link">
			<?= $this->Html->link(
				'Change Password',
				[
					'prefix' => false,
					'controller' => 'Users',
					'action' => 'change_password'
				]
			) ?>
		</li>
		<li class="link">
			<?= $this->Html->link(
				'Logout',
				[
					'prefix' => false,
					'controller' => 'Users',
					'action' => 'logout'
				]
			) ?>
		</li>
	<?php endif; ?>
</ul>