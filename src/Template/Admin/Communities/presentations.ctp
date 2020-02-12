<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Community $community
 * @var mixed $presentations
 * @var array $products
 * @var mixed $purchasedProductIds
 * @var string $titleForLayout
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<div id="presentations-form">
    <?= $this->Form->create($community) ?>
    <?php foreach ($presentations as $productId => $letter): ?>
        <?php
            $status = $community->{'presentation_' . $letter . '_scheduled'};
            $class = ($status == 1) ? 'well show-date' : 'well';
        ?>
        <section class="<?= $class ?>">
            <h2>
                Presentation <?= strtoupper($letter) ?>
            </h2>
            <?php if ($status === 'opted-out'): ?>
                <span class="label label-success">
                    Opted out of <?= $products[$productId] ?>
                </span>
            <?php else: ?>
                <?php
                    $purchased = in_array($productId, $purchasedProductIds);
                    $options = [
                        0 => 'Not scheduled yet',
                        'opted-out' => 'Opted out of ' . $products[$productId]
                    ];
                    if ($purchased) {
                        $options[1] = 'Scheduled';
                    }
                ?>
                <div class="checkbox">
                    <?= $this->Form->radio(
                        'presentation_' . $letter . '_scheduled',
                        $options
                    ) ?>
                </div>
                <?php if ($purchased): ?>
                    <?= $this->Form->input(
                        'presentation_' . $letter,
                        ['label' => false]
                    ) ?>
                <?php else: ?>
                    <span class="label label-warning">
                        Not yet purchased
                    </span>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
    <?= $this->Form->button(
        'Update',
        ['class' => 'btn btn-primary']
    ) ?>
    <?= $this->Form->end() ?>
</div>

<?php $this->element('script', ['script' => 'admin/presentations-form']); ?>
<?php $this->append('buffered'); ?>
    presentationsForm.init();
<?php $this->end();
