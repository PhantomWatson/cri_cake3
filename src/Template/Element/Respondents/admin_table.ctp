<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Response[]|\Cake\Collection\CollectionInterface $responses
 */
    use Cake\Validation\Validation;
    use App\Controller\Component\SurveyProcessingComponent;

    $alignmentSum = SurveyProcessingComponent::getAlignmentSum($responses, $alignmentField);
    $approvedCount = SurveyProcessingComponent::getApprovedCount($responses);
    $totalAlignment = $approvedCount ? round($alignmentSum / $approvedCount) : 0;

    if (! function_exists('getRankClass')) {
        function getRankClass($respondentRank, $actualRank) {
            $difference = abs($respondentRank - $actualRank);
            if ($difference > 2) {
                return 'incorrect';
            } elseif ($difference > 0) {
                return 'near';
            } else {
                return 'correct';
            }
        }
    }
?>

<div class="responses">
    <div class="scrollable_table">
        <table class="table" id="pwrrr-alignment-breakdown">
            <thead class="actual">
                <td colspan="3">
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
                    <th></th>
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
                        <td colspan="11">
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
                        <td>
                            <button class="full-response-button btn btn-default" data-respondent-id="<?= $response['respondent_id'] ?>" title="Show full response">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                        </td>

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
                            <td class="<?= getRankClass($response[$sector.'_rank'], $area["{$sector}_rank"]) ?>">
                                <?= $response[$sector.'_rank'] ?>
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
            <tfoot>
                <tr>
                    <th colspan="3">
                        Average
                    </th>
                    <?php foreach ($sectors as $sector): ?>
                        <td class="<?= getRankClass($averageRanks[$sector], $area["{$sector}_rank"]) ?>">
                            <?= $averageRanks[$sector] ?>
                        </td>
                    <?php endforeach; ?>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <th colspan="3">
                        Order
                    </th>
                    <?php foreach ($sectors as $sector): ?>
                        <td>
                            <?= $rankOrder[$sector] ?>
                        </td>
                    <?php endforeach; ?>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
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

    <?php if ($unaddressedUnapprovedCount): ?>
        <p>
            <?= $this->Html->link(
                "Review and approve $unaddressedUnapprovedCount unapproved " .
                    __n('response', 'responses', $unaddressedUnapprovedCount),
                [
                    'prefix' => 'admin',
                    'controller' => 'Respondents',
                    'action' => 'unapproved',
                    $survey['id']
                ]
            ) ?>
        </p>
    <?php endif; ?>
</div>
