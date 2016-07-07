<?php $this->append('buffered'); ?>
    $('#<?= $areaScope ?>_pwr_table_toggler').click(function (event) {
        event.preventDefault();
        $('#<?= $areaScope ?>_pwr_table').slideToggle();
    });
<?php $this->end(); ?>

<div id="<?= $areaScope ?>_pwr_table" class="toggled_pwr_table">
    <table class="table pwr">
        <thead>
            <tr>
                <th colspan="2">
                    Category
                </th>
                <th>
                    Score
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pwrTable[$areaScope] as $broadCategory => $specificCategories): ?>
                <?php $firstRowInBc = true; ?>
                <?php foreach ($specificCategories as $specificCategory => $score): ?>
                    <tr>
                        <?php if ($firstRowInBc): ?>
                            <th rowspan="<?= count($specificCategories) ?>">
                                <?= $broadCategory ?>
                            </th>
                        <?php endif; ?>
                        <td>
                            <?= $specificCategory ?>
                        </td>
                        <td>
                            <?php if ($score > 1): ?>
                                <span class="above_average">
                                    <?= $score ?>
                                </span>
                            <?php elseif ($score < 1): ?>
                                <span class="below_average">
                                    <?= $score ?>
                                </span>
                            <?php else: ?>
                                <?= $score ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php $firstRowInBc = false; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
