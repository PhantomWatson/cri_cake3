<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<div id="CommunityAdminProgressForm">
    <?php
        echo $this->Form->create($community);

        $steps = [1, 2, 3, 4];
        foreach ($steps as $step) {
            echo $this->Form->radio(
                'score',
                [$step => "Step $step"],
                ['hiddenField' => false]
            );
            if (isset($criteria[$step])) {
                echo '<ul>';
                foreach ($criteria[$step] as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    list($criterion, $passed) = $item;
                    if ($passed) {
                        echo '<li class="pass"><span class="glyphicon glyphicon-ok"></span>';
                    } else {
                        echo '<li class="fail"><span class="glyphicon glyphicon-remove"></span>';
                    }
                    echo $criterion.'</li>';
                }
                echo '</ul>';
            }
        }
    ?>

    <?= $this->Form->button(
        'Update',
        ['class' => 'btn btn-primary']
    ) ?>
    <?= $this->Form->end() ?>
</div>
