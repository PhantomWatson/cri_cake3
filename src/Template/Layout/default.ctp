<?php
	$this->extend('DataCenter.default');
	$this->assign('sidebar', $this->element('Sidebar/sidebar'));
	echo $this->Html->script('script.min.js', ['block' => 'scriptBottom']);
?>

<?php $this->start('subsite_title'); ?>
	<?php // Deliberately blank ?>
<?php $this->end(); ?>

<?php $this->append('scriptBottom'); ?>
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
	<script>$.fn.modal || document.write('<script src="/bootstrap/js/bootstrap.min.js"><\/script>')</script>
<?php $this->end(); ?>

<div id="content">
	<?= $this->element('flash_messages_bootstrap') ?>
	<div id="cri_main">
		<?= $this->fetch('content') ?>
	</div>
</div>