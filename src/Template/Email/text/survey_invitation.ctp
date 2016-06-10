<?php
    $toParticipateBlurb = "to participate in a Community Readiness Initiative ($criUrl) questionnaire for your community";
    echo 'You have been invited ';
    if (empty($clients)) {
        echo "$toParticipateBlurb.";
    } elseif (count($clients) == 1) {
        echo 'by ';
        echo ($clients[0]['salutation'] == '') ? '' : $clients[0]['salutation'].' ';
        echo "{$clients[0]['name']} ({$clients[0]['email']}) $toParticipateBlurb.";
    } else {
        echo "by the following community representatives $toParticipateBlurb:\n";
        foreach ($clients as $client) {
            echo ' - ';
            echo ($client['salutation'] == '') ? '' : $client['salutation'].' ';
            echo "{$client['name']} ({$client['email']})\n";
        }
    }
?>

This is a project in partnership with the Office of Community and Rural Affairs (OCRA) and Ball State University's Indiana
Communities Institute. The questionnaire should take about 15 minutes to complete.

To participate, please visit the following URL within the next five days:

<?= $surveyUrl ?>

<?php
    if (empty($clients)) {
        echo 'If you have any questions, please email cri@bsu.edu.';

    } elseif (count($clients) == 1) {
        echo 'If you have any questions, please contact ';
        echo ($clients[0]['salutation'] == '') ? '' : $clients[0]['salutation'].' ';
        echo "{$clients[0]['name']} at {$clients[0]['email']} ";
        echo 'or email cri@bsu.edu.';
    } else {
        echo 'If you have any questions, please contact your community representatives or email cri@bsu.edu.';
    }
?>
