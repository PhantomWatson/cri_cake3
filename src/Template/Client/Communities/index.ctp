<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php $this->ClientHome->setUserRole($authUser['role']); ?>

<div id="client_home">
    <table>
        <?= $this->element('ClientHome/step1') ?>
        <?php if (! in_array(\App\Model\Table\ProductsTable::OFFICIALS_SURVEY, $optOuts)): ?>
            <?= $this->element('ClientHome/step2') ?>
            <?= $this->element('ClientHome/step3') ?>
            <?= $this->element('ClientHome/step4') ?>
        <?php endif; ?>
    </table>
</div>

<?php $this->element('script', ['script' => 'client']); ?>
<?php $this->append('buffered'); ?>
    clientHome.init();
<?php $this->end();
