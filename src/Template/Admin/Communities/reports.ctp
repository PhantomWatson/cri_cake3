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
?>

<table class="table" id="report">
    <thead>
        <tr class="survey-group-header">
            <td colspan="3"></td>
            <th colspan="14">
                Community Leadership
            </th>
            <th colspan="13">
                Community Leadership
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

            <th>
                Invitations
            </th>
            <th>
                Responses
            </th>
            <th>
                Completion Rate
            </th>
            <th>
                Alignment Calculated
            </th>
            <th>
                Average Alignment
            </th>
            <?php foreach ($sectors as $sector): ?>
                <th>
                    <?= ucwords($sector) ?>
                </th>
            <?php endforeach; ?>
            <th>
                Overall
            </th>
            <th>
                Presentation A Given
            </th>
            <th>
                Presentation B Given
            </th>
            <th>
                Status
            </th>

            <th>
                Invitations
            </th>
            <th>
                Responses
            </th>
            <th>
                Completion Rate
            </th>
            <th>
                Alignment Calculated
            </th>
            <th>
                Average Alignment
            </th>
            <?php foreach ($sectors as $sector): ?>
                <th>
                    <?= $sector ?>
                </th>
            <?php endforeach; ?>
            <th>
                Overall
            </th>
            <th>
                Presentation C Given
            </th>
            <th>
                Status
            </th>
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
