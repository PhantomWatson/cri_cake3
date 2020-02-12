<?php
/**
 * @var \App\View\AppView $this
 * @var string $titleForLayout
 */
?>
<!DOCTYPE html>
<html class="report-layout" lang="en">
    <head>
        <?= $this->Html->charset() ?>
        <link rel="dns-prefetch" href="//ajax.googleapis.com" />
        <title>
            <?php
                if (isset($titleForLayout) && $titleForLayout) {
                    echo $titleForLayout;
                }
            ?>
        </title>
        <meta name="description" content="" />
        <meta name="author" content="Center for Business and Economic Research, Ball State University" />
        <meta name="language" content="en" />
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="imagetoolbar" content="false" />
        <link rel="shortcut icon" href="/data_center/img/favicon.ico" />
        <link href="//fonts.googleapis.com/css?family=Asap:400,400italic,700" rel="stylesheet" type="text/css">
        <?= $this->Html->css('style') ?>
        <?= $this->fetch('css') ?>
        <?= $this->fetch('scriptTop') ?>
    </head>
    <body class="report-layout">
        <main>
            <?= $this->fetch('content') ?>
        </main>

        <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/data_center/js/jquery-1.9.1.min.js"><\/script>')</script>

        <?= $this->Html->script('/data_center/js/datacenter.js') ?>
        <?= $this->element('DataCenter.analytics') ?>
        <?= $this->fetch('scriptBottom') ?>
        <?= $this->fetch('script') ?>

        <script>
            $(document).ready(function () {
                <?= $this->fetch('buffered') ?>
            });
        </script>
    </body>
</html>
