<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<div id="communities_index">
    <?php if (empty($communities)): ?>
        <p class="alert alert-info">
            Sorry, in this early stage of the Community Readiness Initiative, no information about community progress is available yet.
            Please check back later.
        </p>
    <?php else: ?>
        <p>
            The following are all of the communities that have signed up to
            participate in the Community Readiness Initiative, as well as their
            progress through <button class="btn-link" id="explain_steps">CRI's five-step process</button>.
        </p>
        <div class="steps">
            <dl>
                <?php foreach ($steps as $i => $step): ?>
                    <dt>
                        Step <?= $i + 1 ?>:
                    </dt>
                    <dd>
                        <?= $step ?>
                    </dd>
                <?php endforeach; ?>
            </dl>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>
                        Community
                    </th>
                    <?php for ($n = 1; $n <= 5; $n++): ?>
                        <th>
                            Step <?= $n ?>
                        </th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($communities as $community): ?>
                    <tr <?= ($community->score == 0 ? 'class="no_score"' : '') ?>>
                        <th>
                            <?= $community->name ?>
                        </th>
                        <?php for ($n = 1; $n <= 5; $n++): ?>
                            <td>
                                <?php if ($n == 1 || $community->score >= $n): ?>
                                    <span class="glyphicon glyphicon-ok"></span>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php $this->append('buffered'); ?>
    $('#explain_steps').click(function (event) {
       event.preventDefault();
       $('div.steps').slideToggle();
    });
<?php $this->end(); ?>