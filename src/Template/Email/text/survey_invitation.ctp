<?php
    $toParticipateBlurb = "to participate in a Community Readiness Initiative ($criUrl) survey for your community";
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
Communities Institute. We would really appreciate your participation in the survey, which should take no more than ten minutes
of your time.

Please visit the following URL to begin:

<?= $surveyUrl ?>


If you have any questions, please email cri@bsu.edu.
