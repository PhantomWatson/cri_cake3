<div class="employment_growth_table">
    <table>
        <thead>
            <tr>
                <th>
                    Sectors
                </th>
                <th>
                    <?= $table['earlier_year'] ?>
                </th>
                <th>
                    <?= $table['later_year'] ?>
                </th>
                <th>
                    Change
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($table['rows'] as $row): ?>
                <tr>
                    <td>
                        <?= $row['label'] ?>
                    </td>
                    <td>
                        <?= number_format($row[$table['earlier_year']]) ?>
                    </td>
                    <td>
                        <?= number_format($row[$table['later_year']]) ?>
                    </td>
                    <td>
                        <?= $row['change'] ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
