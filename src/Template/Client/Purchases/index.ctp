<div class="page-header">
    <h1>
        <?= $titleForLayout; ?>
    </h1>
</div>

<table class="table" id="purchase_status">
    <thead>
        <tr>
            <th>
                Product
            </th>
            <th>
                Status
            </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td>
                    <?= $product['description'] ?>
                    <span class="price">
                        <?= $product['price'] ?>
                    </span>
                </td>
                <td>
                    <?php
                        list($code, $message) = $product['status'];
                        if ($code == 0):
                    ?>
                        <span class="text-muted">
                            <span class="glyphicon glyphicon-remove"></span>
                            <?= $message ?>
                        </span>
                    <?php elseif ($code == 1): ?>
                        <a class="btn btn-primary" href="<?= $product['status'][2] ?>">
                            Purchase
                        </a>
                    <?php elseif ($code == 2): ?>
                        <span class="text-success">
                            <span class="glyphicon glyphicon-ok"></span>
                            Purchased
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
