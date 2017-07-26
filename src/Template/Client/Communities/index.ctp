<?php
/**
 * @var \App\View\AppView $this
 */
?>
<?php use App\Model\Table\ProductsTable; ?>

<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php $this->ClientHome->setUserRole($authUser['role']); ?>

<div id="client_home">
    <table>
        <?php
            echo $this->element('ClientHome/step1');
            $dropout = false;
            if (in_array(ProductsTable::OFFICIALS_SURVEY, $optOuts)) {
                $dropout = true;
            } else {
                echo $this->element('ClientHome/step2');
                if (in_array(ProductsTable::ORGANIZATIONS_SURVEY, $optOuts)) {
                    $dropout = true;
                } else {
                    echo $this->element('ClientHome/step3');
                    if (in_array(ProductsTable::POLICY_DEVELOPMENT, $optOuts)) {
                        $dropout = true;
                    } else {
                        echo $this->element('ClientHome/step4');
                    }
                }
            }
        ?>
    </table>
    <?php if ($dropout): ?>
        <div class="alert alert-info">
            <p>
                You have opted out of any further participation in the Community Readiness Initiative.
            </p>
            <p>
                If you would like to resume participation, please contact us at
                <a href="mailto:cri@bsu.edu">cri@bsu.edu</a>.
            </p>
        </div>
    <?php endif; ?>
</div>

<?php $this->element('script', ['script' => 'client/client-home']); ?>
<?php $this->append('buffered'); ?>
    clientHome.init();
<?php $this->end();
