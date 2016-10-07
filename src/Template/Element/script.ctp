<?php
    use Cake\Core\Configure;
    $useMin = Configure::read('debug') && file_exists(WWW_ROOT.'js'.DS."$script.min.js");
    $filename = $script.($useMin ? '.min' : '');
    $this->Html->script($filename, ['block' => 'scriptBottom']);