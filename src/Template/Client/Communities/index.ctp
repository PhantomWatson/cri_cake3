<?php use App\Model\Table\ProductsTable; ?>

<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php $this->ClientHome->setUserRole($authUser['role']); ?>

<div id="client_home">
    <table>
        <?= $this->element('ClientHome/step1') ?>
        <?php if (! in_array(ProductsTable::OFFICIALS_SURVEY, $optOuts)): ?>
            <?= $this->element('ClientHome/step2') ?>
            <?php if (! in_array(ProductsTable::ORGANIZATIONS_SURVEY, $optOuts)): ?>
                <?= $this->element('ClientHome/step3') ?>
                <?php if (! in_array(ProductsTable::POLICY_DEVELOPMENT, $optOuts)): ?>
                    <?= $this->element('ClientHome/step4') ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </table>
</div>

<?php $this->element('script', ['script' => 'client']); ?>
<?php $this->append('buffered'); ?>
    clientHome.init();
<?php $this->end();
