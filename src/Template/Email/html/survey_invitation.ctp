<?php
    $toParticipateBlurb = "to participate in a <a href=\"$criUrl\">Community Readiness Initiative</a> questionnaire for your community";
?>

<?php if (empty($clients)): ?>
    <p>
        You have been invited <?= $toParticipateBlurb ?>.
    </p>
<?php elseif (count($clients) == 1): ?>
    <p>
        You have been invited by
        <a href="mailto:<?= $clients[0]['email'] ?>"><?php
            echo ($clients[0]['salutation'] == '') ? '' : $clients[0]['salutation'].' ';
            echo $clients[0]['name'];
        ?></a>
        <?= $toParticipateBlurb ?>.
    </p>
<?php else: ?>
    <p>
        You have been invited by the following community representatives <?= $toParticipateBlurb ?>:
    </p>
    <ul>
        <?php foreach ($clients as $client): ?>
            <li>
                <a href="mailto:<?= $client['email'] ?>"><?php
                    echo ($client['salutation'] == '') ? '' : $client['salutation'].' ';
                    echo $client['name']
                 ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p>
    This is a project in partnership with the
    <a href="http://www.in.gov/ocra/">Indiana Office of Community and Rural Affairs (OCRA)</a>
    and Ball State University's
    <a href="http://bsu.edu/ici">Indiana Communities Institute</a>. We would really appreciate your participation in the questionnaire,
    which should take no more than ten minutes of your time.
</p>

<p>
    Please visit the following URL to begin:
</p>

<p style="font-weight: bold; text-align: center;">
    <a href="<?= $surveyUrl ?>">
        <?= $surveyUrl ?>
    </a>
</p>

<p>
    If you have any questions, please email
    <a href="mailto:cri@bsu.edu">cri@bsu.edu</a>.
</p>
