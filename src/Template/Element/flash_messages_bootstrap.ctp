<?php
	/* This creates the hidden #flash_messages container and fills it with
	 * flash messages and displayed via a javascript animation if there are
	 * messages to display. Regardless, the container is put onto the page
	 * so that asyncronous activity can load messages into it as needed. */
	if (! empty($flashMessages)) {
		$this->append('buffered');
		  echo 'flashMessage.init();';
        $this->end();
	}
?>
<div id="flash_messages_bootstrap" style="display: none;">
	<?php if (! empty($flashMessages)): ?>
		<?php foreach ($flashMessages as $msg): ?>
			<?php
				switch ($msg['class']) {
					case 'error':
						$bootstrapClass = 'alert-danger';
						break;
					case 'success':
						$bootstrapClass = 'alert-success';
						break;
					default:
						$bootstrapClass = 'alert-info';
				}
			?>
			<div class="alert alert-dismissible <?= $bootstrapClass ?>" role="alert">
				<button type="button" class="close" data-dismiss="alert">
					<span aria-hidden="true">&times;</span>
				</button>
				<?= $msg['message'] ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>