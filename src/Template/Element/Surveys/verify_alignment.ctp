<?php
    use App\Controller\Component\SurveyProcessingComponent;
    $approvedCount = SurveyProcessingComponent::getApprovedCount($responses);
    $savedAlignmentError = false;
    if ($approvedCount) {
        foreach (['local', 'parent'] as $areaType) {
            $alignmentSum = SurveyProcessingComponent::getAlignmentSum(
                $responses,
                $areaType . '_area_pwrrr_alignment'
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
            has been saved as
            <?= $savedAlignmentError['savedAlignment'] ?>%,
            but the most recent calculation suggests that it should be updated to
            <?= $savedAlignmentError['calculatedAlignment'] ?>%.
        </p>
        <p>
            <button id="update-alignment" class="btn btn-default" data-update-url="<?= $updateUrl ?>">
                Update alignment
            </button>
        </p>
    </div>
<?php endif; ?>
