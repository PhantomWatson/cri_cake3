<p>
    You have been invited to participate in a
    <a href="<?= $criUrl ?>">
        Community Readiness Initiative
    </a>
    survey for your community.
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
