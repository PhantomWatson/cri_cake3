<?php
/**
 * @var \App\View\AppView $this
 * @var string $homeUrl
 * @var string $userName
 * @var string $communityName
 * @var string $toStep
 */
?>
<p>
    <?= $userName ?>,
</p>

<p>
    <?= $communityName ?> has been advanced to Step <?= $toStep ?> of the
    <a href="<?= $homeUrl ?>">Community Readiness Initiative</a>. For more information about this phase of the CRI
    process, please log in and visit the CRI Client Home page.
</p>

<p>
    If you have any questions, please email <a href="mailto:cri@bsu.edu">cri@bsu.edu</a>.
</p>
