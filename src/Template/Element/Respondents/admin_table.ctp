<?php
    use Cake\Validation\Validation;
    use App\Controller\Component\SurveyProcessingComponent;

    $alignmentSum = SurveyProcessingComponent::getAlignmentSum($responses, $alignmentField);
    $approvedCount = SurveyProcessingComponent::getApprovedCount($responses);
    $totalAlignment = $approvedCount ? round($alignmentSum / $approvedCount) : 0;
?>

<div class="table_container">
    <table class="table">
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
                    <?php
                        $arrow = getSortArrow('response_date', $this->request->params);
                        echo $this->Paginator->sort('response_date', 'Date'.$arrow, ['escape' => false]);
                    ?>
                </th>
                <th>
                    Revisions
                </th>
                <?php foreach ($sectors as $sector): ?>
                    <th>
                        <?php
                            $arrow = getSortArrow($sector.'_rank', $this->request->params);
                            echo $this->Paginator->sort($sector.'_rank', ucwords($sector).$arrow, ['escape' => false]);
                        ?>
                    </th>
                <?php endforeach; ?>
                <th>
                    Alignment
                </th>
                <th>
                    <?php
                        $arrow = getSortArrow('Respondent.approved', $this->request->params);
                        echo $this->Paginator->sort('Respondent.approved', 'Approved'.$arrow, ['escape' => false]);
                    ?>
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
                <tr class="response">
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

                    <td>
                        <?php if ($response['respondent']['approved'] == 1): ?>
                            <span class="glyphicon glyphicon-ok"></span>
                        <?php else: ?>
                            <span class="glyphicon glyphicon-remove"></span>
                        <?php endif; ?>
                    </td>

                    <td class="selected">
                        <?php $checked = ($response['respondent']['approved'] == 1) ? 'checked' : ''; ?>
                        <input type="checkbox" class="custom_alignment_calc" data-alignment="<?= $response[$alignmentField] ?>" <?= $checked ?> />
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7">
                    Calculated total alignment
                    <br />
                    <a href="#" class="toggle_custom_calc">
                        Edit what responses are used in this calculation
                    </a>
                </td>
                <td>
                    <?= $totalAlignment ?>%
                </td>
                <td>
                </td>
                <td class="selected">
                    <?= $totalAlignment ?>%
                </td>
            </tr>
        </tfoot>
    </table>
</div>
