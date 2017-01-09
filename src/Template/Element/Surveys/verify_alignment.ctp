<?php
    use App\Controller\Component\SurveyProcessingComponent;
    $approvedCount = SurveyProcessingComponent::getApprovedCount($responses);
    $savedAlignmentError = false;
    if ($approvedCount) {
        foreach (['local', 'parent'] as $areaType) {
            $alignmentSum = SurveyProcessingComponent::getAlignmentSum(
                $responses,
                'alignment_vs_' . $areaType
            );
            $savedAlignment = $survey->{'alignment_vs_' . $areaType};
            $calculatedAlignment = (int)($alignmentSum / $approvedCount);
            if ($savedAlignment != $calculatedAlignment) {
                $savedAlignmentError = compact('areaType', 'savedAlignment', 'calculatedAlignment');
                break;
            }
        }
    }
    $updateUrl = \Cake\Routing\Router::url([
        'prefix' => 'admin',
        'controller' => 'Surveys',
        'action' => 'updateAlignment',
        $survey['id']
    ]);
?>

<?php if ($savedAlignmentError): ?>
    <div class="alert alert-danger">
        <p>
            <strong>
                Attention:
            </strong>
            The total alignment of these responses versus the actual PWR<sup>3</sup> rankings for
            <?= $community[$savedAlignmentError['areaType'] . '_area']['name'] ?>
            <?php if ($savedAlignmentError['savedAlignment']): ?>
                has been saved as <?= $savedAlignmentError['savedAlignment'] ?>%,
                but the most recent calculation suggests that it should be updated to
                <?= $savedAlignmentError['calculatedAlignment'] ?>%.
            <?php else: ?>
                has not been saved to the database. Click the button below to save this community's
                alignment as <?= $savedAlignmentError['calculatedAlignment'] ?>%.
            <?php endif; ?>
        </p>
        <p>
            <button id="update-alignment" class="btn btn-default" data-update-url="<?= $updateUrl ?>">
                <?php if ($savedAlignmentError['savedAlignment']): ?>
                    Update alignment
                <?php else: ?>
                    Save alignment
                <?php endif; ?>
            </button>
        </p>
    </div>
<?php endif; ?>
