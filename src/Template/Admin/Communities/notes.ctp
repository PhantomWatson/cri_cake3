<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Community $community
 * @var string $titleForLayout
 */
?>
<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php
    echo $this->Form->create($community);
    echo $this->Form->control('notes');
    echo $this->Form->button(
        'Update',
        ['class' => 'btn btn-primary']
    );
    echo $this->Form->end();
    $this->element('script', ['script' => 'form-protector']);
?>

<?php $this->append('buffered'); ?>
    formProtector.protect('notes', {});
<?php $this->end();
