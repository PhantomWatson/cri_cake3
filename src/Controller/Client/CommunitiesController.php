<?php
namespace App\Controller\Client;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class CommunitiesController extends AppController
{
    /**
     * Client home page
     */
    public function index()
    {
        $clientId = $this->getClientId();
        $communityId = $this->Communities->getClientCommunityId($clientId);

        if ($communityId) {
            try {
                $community = $this->Communities->get($communityId);
            } catch (RecordNotFoundException $e) {
                $this->set('titleForLayout', 'CRI Account Not Yet Ready For Use');
                return $this->render('notready');
            }
        } else {
            $this->set('titleForLayout', 'CRI Account Not Yet Ready For Use');
            return $this->render('notready');
        }

        $purchaseUrls = [];
        $productsTable = TableRegistry::get('Products');
        for ($productId = 1; $productId <= 5; $productId++) {
            $purchaseUrls[$productId] = $productsTable->getPurchaseUrl($productId, $clientId, $communityId);
        }

        $criteria = $this->Communities->getProgress($communityId);

        // Assign criteria that might be in either whole-number steps or half-steps to variables
        $step2Alignment = false;
        foreach ([2, '2.5'] as $step) {
            if (isset($criteria[$step]['alignment_passed'])) {
                $step2Alignment = $criteria[$step]['alignment_passed'];
            }
        }
        $step2SurveyPurchased = false;
        foreach ([2, '2.5'] as $step) {
            if (isset($criteria[$step]['survey_purchased'])) {
                $step2SurveyPurchased = $criteria[$step]['survey_purchased'];
            }
        }
        $step3Alignment = false;
        foreach ([3, '3.5'] as $step) {
            if (isset($criteria[$step]['alignment_passed'])) {
                $step3Alignment = $criteria[$step]['alignment_passed'];
            }
        }
        $step3PolicyDevPurchased = false;
        foreach ([3, '3.5'] as $step) {
            if (isset($criteria[$step]['policy_dev_purchased'])) {
                $step3PolicyDevPurchased = $criteria[$step]['policy_dev_purchased'];
            }
        }

        $surveysTable = TableRegistry::get('Surveys');
        $officialSurveyId = $surveysTable->getSurveyId($communityId, 'official');
        $organizationSurveyId = $surveysTable->getSurveyId($communityId, 'organization');
        $respondentsTable = TableRegistry::get('Respondents');
        $this->set([
            'titleForLayout' => $communityName.'\'s Progress in the CRI Program',
            'score' => $community->score,
            'officialSurveyOpen' => $surveysTable->isOpen($communityId, 'official'),
            'organizationSurveyOpen' => $surveysTable->isOpen($communityId, 'organization'),
            'officialUninvitedRespondents' => $respondentsTable->getUninvitedCount($officialSurveyId),
            'officialResponsesChecked' => $surveysTable->getChecked($officialSurveyId),
            'organizationResponsesChecked' => $surveysTable->getChecked($organizationSurveyId),
            'fastTrack' => $community->fast_track,
            'autoImportFrequency' => $surveysTable->getPerSurveyAutoImportFrequency()
        ]);
        $this->set(compact(
            'criteria',
            'purchaseUrls',
            'officialSurveyId',
            'organizationSurveyId',
            'step2Alignment',
            'step2SurveyPurchased',
            'step3Alignment',
            'step3PolicyDevPurchased'
        ));
    }
}
