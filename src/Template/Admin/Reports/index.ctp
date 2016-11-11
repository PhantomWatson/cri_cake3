<?php
    function surveyInfo($survey, $type, $sectors) {
        $retval = [
            $survey['invitations'],
            $survey['responses'],
            $survey['responseRate'],
            $survey['alignment'] ? $survey['alignment'] : 'Not calculated'
        ];
        foreach ($sectors as $sector) {
            $retval[] = $survey['internalAlignment'][$sector];
        }
        $retval[] = $survey['internalAlignment']['total'];
        $openTag = '<td class="survey" data-survey-type="' . $type . '">';
        return  $openTag . implode('</td>' . $openTag, $retval) . '</td>';
    }
    function surveyHeader($sectors, $type) {
        $cells = [
            'Invitations',
            'Responses',
            'Completion Rate',
            'Average Alignment',
        ];
        foreach ($sectors as $sector) {
            $cells[] = ucwords($sector);
        }
        $cells[] = 'Overall';
        if ($type == 'officials') {
            $cells[] = 'Presentation A Given';
            $cells[] = 'Presentation B Given';
        } else {
            $cells[] = 'Presentation C Given';
        }
        $cells[] = 'Status';
        $retval = '';
        foreach ($cells as $cell) {
            // Build CSS class string
            $class = 'survey';
            if (in_array(strtolower($cell), $sectors) || $cell == 'Overall') {
                $class .= ' int-alignment';
            }
            if ($cell == 'Production') {
                $class .= ' int-alignment-left-edge';
            } elseif ($cell == 'Overall') {
                $class .= ' int-alignment-right-edge';
            } elseif ($cell == 'Status') {
                $class .= ' survey-status';
            }

            // Abbreviate
            $abbreviations = [
                'Production' => 'P',
                'Wholesale' => 'W',
                'Retail' => 'Ret',
                'Residential' => 'Res',
                'Recreation' => 'Rec'
            ];
            if (isset($abbreviations[$cell])) {
                $cell = $abbreviations[$cell];
            }

            $retval .= '<th class="' . $class . '" data-survey-type="' . $type . '">';
            $retval .= $cell;
            $retval .= '</th>';
        }
        $retval .=
            '<th class="minimized-status-header" data-survey-type="' . $type . '">' .
            '<button class="survey-toggler btn btn-link" data-survey-type="' . $type . '">' .
            (($type == 'officials') ? 'Community Leadership Status' : 'Community Organizations Status') .
            '</button>' .
            '</th>';
        return $retval;
    }
?>

<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<section>
    <h2>
        OCRA Report
    </h2>
    <p>
        The OCRA Report excludes PWR<sup>3</sup> and internal alignment calculations, but is otherwise the same as
        the admin version of the report.
        <br />
        <?php $icon = '<img src="/data_center/img/icons/document-excel-table.png" alt="Microsoft Excel (.xlsx)" />'; ?>
        <?= $this->Html->link(
            $icon . ' Download OCRA Report',
            ['action' => 'ocra'],
            [
                'class' => 'btn btn-default',
                'escape' => false,
                'title' => 'Download an OCRA version of this report as a Microsoft Excel (.xlsx) file'
            ]
        ) ?>
    </p>
</section>

<section>
    <h2>
        Admin Report
    </h2>
    <p>
        The admin report can be viewed in your browser below or downloaded as a spreadsheet. Below, click on each survey
        type to expand and see more details.
        <br />
        <?= $this->Html->link(
            $icon . ' Download Admin Report',
            ['action' => 'admin'],
            [
                'class' => 'btn btn-default',
                'escape' => false,
                'title' => 'Download the version of this report for CRI administrators as a Microsoft Excel (.xlsx) file'
            ]
        ) ?>
    </p>

    <table class="table" id="report">
        <colgroup>
            <col span="1" />
        </colgroup>
        <colgroup class="survey">
            <col span="13" />
        </colgroup>
        <colgroup class="survey">
            <col span="12" />
        </colgroup>
        <thead>
            <tr class="survey-group-header">
                <td colspan="3"></td>
                <th colspan="14" data-full-colspan="14" data-survey-type="officials" class="survey">
                    <button class="survey-toggler btn btn-link" data-survey-type="officials">
                        Community Leadership
                    </button>
                </th>
                <th colspan="13" data-full-colspan="13" data-survey-type="organizations" class="survey">
                    <button class="survey-toggler btn btn-link" data-survey-type="organizations">
                        Community Organizations
                    </button>
                </th>
            </tr>
            <tr class="internal-alignment-headers">
                <td colspan="1"></td>
                <td colspan="1" class="spacer"></td>
                <td colspan="4" data-survey-type="officials" class="empty"></td>
                <th colspan="6" data-survey-type="officials">
                    Internal Alignment
                </th>
                <td colspan="3" data-survey-type="officials" class="empty"></td>
                <td colspan="4" data-survey-type="organizations" class="empty"></td>
                <th colspan="6" data-survey-type="organizations">
                    Internal Alignment
                </th>
                <td colspan="2" data-survey-type="organizations" class="empty"></td>
            </tr>
            <tr class="general-header">
                <th>
                    Community
                </th>
                <?= surveyHeader($sectors, 'officials'); ?>
                <?= surveyHeader($sectors, 'organizations'); ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report as $community): ?>
                <tr>
                    <td>
                        <?= $community['name'] ?>
                        <br />
                        <span class="area-details">
                            <?= $community['parentArea'] ?>
                        </span>
                        <br />
                        <span class="area-details">
                            <?= $community['parentAreaFips'] ?>
                        </span>
                    </td>

                    <?= surveyInfo($community['official_survey'], 'officials', $sectors); ?>

                    <td class="survey" data-survey-type="officials">
                        <?= $community['presentationsGiven']['a'] ?>
                    </td>
                    <td class="survey" data-survey-type="officials">
                        <?= $community['presentationsGiven']['b'] ?>
                    </td>
                    <td class="survey-status">
                        <?= $community['official_survey']['status'] ?>
                    </td>

                    <?= surveyInfo($community['organization_survey'], 'organizations', $sectors); ?>

                    <td class="survey" data-survey-type="organizations">
                        <?= $community['presentationsGiven']['c'] ?>
                    </td>
                    <td class="survey-status">
                        <?= $community['organization_survey']['status'] ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php $this->append('buffered'); ?>
    adminReport.init();
<?php $this->end(); ?>
