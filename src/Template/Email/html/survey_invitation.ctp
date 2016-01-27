<?php
    $toParticipateBlurb = "to participate in a <a href=\"$criUrl\">Community Readiness Initiative</a> survey for your community";
?>

<?php if (empty($clients)): ?>
    <p>
        You have been invited <?= $toParticipateBlurb ?>.
    </p>
<?php elseif (count($clients) == 1): ?>
    <p>
        You have been invited by
        <a href="mailto:<?= $clients[0]['email'] ?>"><?=
            $clients[0]['name']
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
                <a href="mailto:<?= $client['email'] ?>"><?=
                    $client['name']
                 ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p>
    This is a project in partnership with the
    <a href="http://www.in.gov/ocra/">Indiana Office of Community and Rural Affairs (OCRA)</a>
    and Ball State University's
    <a href="">Indiana Communities Institute</a>. We would really appreciate your participation in the survey,
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

<?= $this->element('Email'.DS.'html'.DS.'signature') ?>
