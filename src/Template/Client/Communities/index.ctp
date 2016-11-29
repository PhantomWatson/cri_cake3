<div class="page-header">
    <h1>
        <?= $titleForLayout ?>
    </h1>
</div>

<?php if ($authUser['role'] == 'admin'): ?>
    <?= $this->element('Communities/admin_header', [
        'adminHeader' => $adminHeader,
        'communityId' => $community->id,
        'surveyId' => null
    ]) ?>
    <?php $this->element('script', ['script' => 'admin']); ?>
<?php endif; ?>

<div id="client_home">
    <table>
        <?= $this->element('ClientHome/step1') ?>
        <?= $this->element('ClientHome/step2') ?>

        <?php if ($fastTrack): ?>
            <?= tbodyForStep(3, $score) ?>
                <tr>
                    <th colspan="3">
                        <button class="step-header">
                            Step Three and Four skipped in Fast Track
                        </button>
                    </th>
                </tr>
            </tbody>
        <?php else: ?>
            <?= $this->element('ClientHome/step3') ?>
            <?= $this->element('ClientHome/step4') ?>
        <?php endif; ?>

        <?= $this->element('ClientHome/step5') ?>
    </table>
</div>

<?php $this->element('script', ['script' => 'client']); ?>
<?php $this->append('buffered'); ?>
    clientHome.init();
<?php $this->end();
