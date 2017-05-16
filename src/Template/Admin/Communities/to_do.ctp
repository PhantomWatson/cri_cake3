<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<table class="table" id="admin-to-do">
    <thead>
        <tr>
            <th>
                Responsible Party
            </th>
            <th>
                Community
            </th>
            <th>
                To Do
            </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($communities as $community): ?>
            <tr>
                <td class="<?= $community->toDo['class'] ?>">
                    <?php
                        switch ($community->toDo['class']) {
                            case 'ready':
                                $icon = 'exclamation-sign';
                                break;
                            case 'waiting':
                                $icon = 'hourglass';
                                break;
                            case 'complete':
                            default:
                                $icon = 'check';
                                break;
                        }
                    ?>
                    <span class="glyphicon glyphicon-<?= $icon ?>"></span>
                    <?= $community->toDo['responsible'] ?>
                </td>
                <td>
                    <?= $community->name ?>
                </td>
                <td class="<?= $community->toDo['class'] ?>">
                    <?= $community->toDo['msg'] ?>
                    <?php if (isset($community->toDo['since'])): ?>
                        for <?= $community->toDo['since'] ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
