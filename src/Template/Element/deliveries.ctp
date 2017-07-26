<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Delivery[]|\Cake\Collection\CollectionInterface $deliveries
 */
?>
<?= $this->element('pagination') ?>

<table class="table">
    <thead>
    <tr>
        <th>
            Community
        </th>
        <th>
            Deliverable
        </th>
        <th>
            Delivered
        </th>
        <th>
            Reported by
        </th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($deliveries as $delivery): ?>
        <tr>
            <td>
                <?= $this->Html->link(
                    isset($delivery->community->name)
                        ? $delivery->community->name
                        : 'Community #' . $delivery->community_id,
                    [
                        'prefix' => 'admin',
                        'controller' => 'Deliveries',
                        'action' => 'community',
                        $delivery->community_id
                    ]
                ) ?>
            </td>
            <td>
                <?= ucfirst($delivery->deliverable->name) ?>
            </td>
            <td>
                <?= $this->Time->format($delivery->created, 'MMM d Y, h:mma', false, 'America/New_York') ?>
            </td>
            <td>
                <?= isset($delivery->user->name)
                    ? $delivery->user->name
                    : 'User #' . $delivery->user_id ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?= $this->element('pagination') ?>
