<?= $userName ?>,

<?= $communityName ?> has been advanced to Step 4 of CRI. The next step in the CRI process for that community is for CBER and ICI to prepare and deliver economic policy development materials to the <?= count($clients) > 1 ? 'clients' : 'client' ?><?= count($clients) > 0 ? ':' : '.' ?><?php if (count($clients) === 1): ?> <?= $clients[0]['name'] ?> (<a href="mailto:<?= $clients[0]['email'] ?>"><?= $clients[0]['email'] ?></a>)<?php endif; ?>

<?php
    if (count($clients) > 1) {
        foreach ($clients as $client) {
            echo ' - ' . $client['name'] . ' (' . $client['email'] . ")\n";
        }
    }
?>

Once this is done, please report those materials delivered by visiting <?= $actionUrl ?>.
