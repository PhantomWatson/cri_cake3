<?php
/**
 * @var \App\View\AppView $this
 * @var string $actionUrl
 * @var array $clients
 * @var string $communityName
 * @var string $userName
 */
/**
 * @var \App\View\AppView $this
 */
?>
<p>
    <?= $userName ?>,
</p>

<p>
    <?= $communityName ?> has been advanced to Step 4 of CRI. The next step in the CRI process for
    that community is for CBER and ICI to prepare and deliver economic policy development materials to the
    <?= count($clients) > 1 ? 'clients' : 'client' ?><?= count($clients) > 0 ? ':' : '.' ?>
    <?php if (count($clients) === 1): ?>
        <?= $clients[0]['name'] ?> (<a href="mailto:<?= $clients[0]['email'] ?>"><?= $clients[0]['email'] ?></a>)
    <?php endif; ?>
</p>

<?php if (count($clients) > 1): ?>
    <ul>
        <?php foreach ($clients as $client): ?>
            <li>
                <?= $client['name'] ?> (<a href="mailto:<?= $client['email'] ?>"><?= $client['email'] ?></a>)
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<p>
    Once this is done, please <a href="<?= $actionUrl ?>">report those materials delivered</a>.
</p>

<?= $this->element('Email/html/admin_email_settings_link') ?>
