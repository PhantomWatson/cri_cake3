<?php
    use Cake\Core\Configure;
    $filename = $script.(Configure::read('debug') ? '' : '.min');
    $this->Html->script($filename, ['block' => 'scriptBottom']);