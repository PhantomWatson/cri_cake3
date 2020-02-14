<?php
/**
 * @var \App\View\AppView $this
 * @var array $clients
 * @var string $surveyUrl
 * @var string $criUrl
 */
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
    <a href="http://bsu.edu/ici">Indiana Communities Institute</a>.
    The questionnaire should take about 15 minutes to complete.
</p>

<p>
    To participate, please visit the following URL within the next five days:
</p>

<p style="font-weight: bold; text-align: center;">
    <a href="<?= $surveyUrl ?>">
        <?= $surveyUrl ?>
    </a>
</p>

<?php if (empty($clients)): ?>
    <p>
        If you have any questions, please email
        <a href="mailto:cri@bsu.edu">cri@bsu.edu</a>.
    </p>
<?php elseif (count($clients) == 1): ?>
    <p>
        If you have any questions, please contact
        <a href="mailto:<?= $clients[0]['email'] ?>"><?php
            echo ($clients[0]['salutation'] == '') ? '' : $clients[0]['salutation'].' ';
            echo $clients[0]['name'];
        ?></a>
        or email <a href="mailto:cri@bsu.edu">cri@bsu.edu</a>.
    </p>
<?php else: ?>
    <p>
        If you have any questions, please contact your community representatives
        or email <a href="mailto:cri@bsu.edu">cri@bsu.edu</a>.
    </p>
<?php endif; ?>
