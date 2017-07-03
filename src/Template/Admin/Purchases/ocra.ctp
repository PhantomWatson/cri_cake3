<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<p>
    <?= $this->Html->link(
        '<span class="glyphicon glyphicon-arrow-left"></span> Back to Payment Records',
        [
            'prefix' => 'admin',
            'controller' => 'Purchases',
            'action' => 'index'
        ],
        [
            'class' => 'btn btn-default',
            'escape' => false
        ]
    ) ?>
</p>

<p class="well">
    The following are the non-refunded charges whose funding source is OCRA. Charges become billable when their
    products (e.g. presentations) are delivered. Select charges and click 'Update' in order to mark charges
    as billed or paid.
</p>

<div>
    <ul class="nav nav-tabs ocra-billing" role="tablist">
        <?php foreach ($purchases as $label => $group): ?>
            <?php
                $hyphenatedLabel = str_replace(' ', '-', $label);
                $first = $label == 'not yet billable';
            ?>
            <li role="presentation" <?php if ($first): ?>class="active"<?php endif; ?>>
                <a href="#<?= $hyphenatedLabel ?>" aria-controls="<?= $hyphenatedLabel ?>" role="tab" data-toggle="tab">
                    <?= ucwords($label) ?>
                    <span class="badge">
                        <?= count($group['purchases']) ?>
                        <?php /* ($<?= number_format($totals[$label]) ?>) */ ?>
                    </span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content ocra-billing">
        <?php foreach ($purchases as $label => $group): ?>
            <?php
                $hyphenatedLabel = str_replace(' ', '-', $label);
                $first = $label == 'not yet billable';
            ?>
            <div role="tabpanel" class="tab-pane <?php if ($first): ?>active<?php endif; ?>" id="<?= $hyphenatedLabel ?>">
                <?php if ($group['purchases']): ?>
                    <?= $this->element('Purchases/ocra_table', compact(
                        'group',
                        'label'
                    )) ?>
                <?php else: ?>
                    <p class="well no-charges">
                        No charges
                    </p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php $this->element('script', ['script' => 'admin/ocra-payments']); ?>
<?php $this->append('buffered'); ?>
    ocraPayments.init();
<?php $this->end(); ?>
