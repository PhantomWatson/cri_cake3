<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    This sets the 'amount' value for any purchase record with amount == zero. This is intended to be run once,
    immediately after the <code>Purchases.amount</code> field is added to the database to solve
    <a href="https://github.com/BallStateCBER/cri/issues/62">this issue</a>.
</p>

<?= $this->Form->create(null) ?>

<?= $this->Form->submit('Continue', [
    'class' => 'btn btn-default'
]) ?>
<?= $this->Form->end() ?>
