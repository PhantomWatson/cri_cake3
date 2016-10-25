<?php
    function surveyInfo($survey, $sectors) {
        $retval = [
            $survey['invitations'],
            $survey['responses'],
            $survey['responseRate'],
            $survey['alignment'] ? 'Yes' : 'No',
            $survey['alignment']
        ];
        foreach ($sectors as $sector) {
            $retval[] = $survey['internalAlignment'][$sector];
        }
        $retval[] = $survey['internalAlignment']['total'];

        return '<td>' . implode('</td><td>', $retval) . '</td>';
    }
    function surveyHeader($sectors, $type) {
        $retval = [
            'Invitations',
            'Responses',
            'Completion Rate',
            'Alignment Calculated',
            'Average Alignment',
        ];
        foreach ($sectors as $sector) {
            $retval[] = ucwords($sector);
        }
        $retval[] = 'Overall';
        if ($type == 'officials') {
            $retval[] = 'Presentation A Given';
            $retval[] = 'Presentation B Given';
        } else {
            $retval[] = 'Presentation C Given';
        }
        $retval[] = 'Status';
        return '<th>' . implode('</th><th>', $retval) . '</th>';
    }
?>

<table class="table" id="report">
    <colgroup>
        <col span="3" />
    </colgroup>
    <colgroup class="survey">
        <col span="14" />
    </colgroup>
    <colgroup class="survey">
        <col span="13" />
    </colgroup>
    <thead>
        <tr class="survey-group-header">
            <td colspan="3"></td>
            <th colspan="14">
                Community Leadership
            </th>
            <th colspan="13">
                Community Organizations
            </th>
        </tr>
        <tr>
            <th>
                Community
            </th>
            <th>
                Area
            </th>
            <th>
                Area FIPS
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
                </td>
                <td>
                    <?= $community['parentArea'] ?>
                </td>
                <td>
                    <?= $community['parentAreaFips'] ?>
                </td>

                <?= surveyInfo($community['official_survey'], $sectors); ?>

                <td>
                    <?= $community['presentationsGiven']['a'] ? 'Yes' : 'No' ?>
                </td>
                <td>
                    <?= $community['presentationsGiven']['b'] ? 'Yes' : 'No' ?>
                </td>
                <td>
                    <?= $community['official_survey']['status'] ?>
                </td>

                <?= surveyInfo($community['organization_survey'], $sectors); ?>

                <td>
                    <?= $community['presentationsGiven']['c'] ? 'Yes' : 'No' ?>
                </td>
                <td>
                    <?= $community['organization_survey']['status'] ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
