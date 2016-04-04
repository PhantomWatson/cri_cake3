<?php
    use Cake\Validation\Validation;
    use App\Controller\Component\SurveyProcessingComponent;

    $alignmentSum = SurveyProcessingComponent::getAlignmentSum($responses, $alignmentField);
    $approvedCount = SurveyProcessingComponent::getApprovedCount($responses);
    $totalAlignment = $approvedCount ? round($alignmentSum / $approvedCount) : 0;
?>

<div class="responses">
    <div class="scrollable_table">
        <table class="table" id="pwrrr-alignment-breakdown">
            <thead class="actual">
                <td colspan="2">
                    Actual rankings
                </td>
                <?php foreach ($sectors as $sector): ?>
                    <td class="actual_rank">
                        <?= $area["{$sector}_rank"] ?>
                    </td>
                <?php endforeach; ?>
                <td>
                </td>
                <td>
                </td>
            </thead>
            <thead>
                <tr>
                    <th>
                        Date
                    </th>
                    <th>
                        Revisions
                    </th>
                    <?php foreach ($sectors as $sector): ?>
                        <th>
                            <?= ucwords($sector) ?>
                        </th>
                    <?php endforeach; ?>
                    <th>
                        Alignment
                    </th>
                    <th>
                        Approved
                    </th>
                    <th class="selected">
                        Selected
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($responses as $response): ?>
                    <tr class="respondent">
                        <td colspan="10">
                            <span class="glyphicon glyphicon-arrow-down"></span>
                            <?php
                                $name = $response['respondent']['name'];
                                echo $name ? $name : '<span class="no_name">No name provided</span>';
                                $title = $response['respondent']['title'];
                                echo $title ? ', '.$title : '';
                                $email = $response['respondent']['email'];
                                echo ' - ';
                                echo Validation::email($email) ? '<a href="mailto:'.$email.'">'.$email.'</a>' : $email;
                            ?>
                        </td>
                    </tr>
                    <tr class="response" data-alignment="<?= $response[$alignmentField] ?>">
                        <td class="date">
                            <?php
                                $timestamp = strtotime($response['response_date']);
                                echo date('n/j/y', $timestamp);
                            ?>
                        </td>

                        <td>
                            <?= $response['revision_count'] ?>
                        </td>

                        <?php foreach ($sectors as $sector): ?>
                            <?php
                                $respondentRank = $response[$sector.'_rank'];
                                $actualRank = $area["{$sector}_rank"];
                                $difference = abs($respondentRank - $actualRank);
                                if ($difference > 2) {
                                    $class = 'incorrect';
                                } elseif ($difference > 0) {
                                    $class = 'near';
                                } else {
                                    $class = 'correct';
                                }
                            ?>
                            <td class="<?= $class ?>">
                                <?= $respondentRank ?>
                            </td>
                        <?php endforeach; ?>

                        <td>
                            <?= $response[$alignmentField] ?>%
                        </td>

                        <td class="approved">
                            <?php if ($response['respondent']['approved'] == 1): ?>
                                <span class="glyphicon glyphicon-ok"></span>
                            <?php else: ?>
                                <span class="glyphicon glyphicon-remove"></span>
                            <?php endif; ?>
                        </td>

                        <td class="selected">
                            <?php $checked = ($response['respondent']['approved'] == 1) ? 'checked' : ''; ?>
                            <input type="checkbox" class="custom_alignment_calc" <?= $checked ?> />
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p class="total_alignment">
        Average alignment of
        <span class="respondent_count">
            <?= $approvedCount ?>
        </span>
        <select class="calc-mode">
            <option value="approved">approved</option>
            <option value="selected">selected</option>
        </select>
        <span class="respondent_plurality"><?=
             __n('respondent', 'respondents', $approvedCount)
        ?></span>:
        <span class="total_alignment">
            <?= $totalAlignment ?>%
        </span>
    </p>
</div>
