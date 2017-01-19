<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php $this->ClientHome->setUserRole($authUser['role']); ?>

<div id="client_home">
    <table>
        <?= $this->element('ClientHome/step1') ?>
        <?= $this->element('ClientHome/step2') ?>
        <?= $this->element('ClientHome/step3') ?>
        <?= $this->element('ClientHome/step4') ?>
    </table>
</div>

<?php $this->element('script', ['script' => 'client']); ?>
<?php $this->append('buffered'); ?>
    clientHome.init();
<?php $this->end();
