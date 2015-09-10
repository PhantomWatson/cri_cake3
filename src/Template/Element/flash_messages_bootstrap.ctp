<?php
	/* This creates the hidden #flash_messages container and fills it with
	 * flash messages and displayed via a javascript animation if there are
	 * messages to display. Regardless, the container is put onto the page
	 * so that asyncronous activity can load messages into it as needed. */
	if (! empty($flash_messages)) {
		$this->append('buffered');
		  echo 'flashMessage.init();';
        $this->end();
	}
?>
<div id="flash_messages_bootstrap" style="display: none;">
	<?php if (! empty($flash_messages)): ?>
		<?php foreach ($flash_messages as $msg): ?>
			<?php
				switch ($msg['class']) {
					case 'error':
						$bootstrap_class = 'alert-danger';
						break;
					case 'success':
						$bootstrap_class = 'alert-success';
						break;
					default:
						$bootstrap_class = 'alert-info';
				}
			?>
			<div class="alert alert-dismissible <?= $bootstrap_class ?>" role="alert">
				<button type="button" class="close" data-dismiss="alert">
					<span aria-hidden="true">&times;</span>
				</button>
				<?= $msg['message'] ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>