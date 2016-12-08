<?php
namespace App\Controller\Client;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class CommunitiesController extends AppController
{
    /**
     * Client home page
     *
     * @return \App\Controller\Response
     */
    public function index()
    {
        $this->viewBuilder()->helpers(['ClientHome']);
        $clientId = $this->getClientId();
        if (! $clientId) {
            return $this->chooseClientToImpersonate();
        }
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
        $step2SurveyPurchased = $criteria[2]['survey_purchased'];
        $step3PolicyDevPurchased = $criteria[3]['policy_dev_purchased'];

        $surveysTable = TableRegistry::get('Surveys');
        $officialSurveyId = $surveysTable->getSurveyId($communityId, 'official');
        $organizationSurveyId = $surveysTable->getSurveyId($communityId, 'organization');
        $importErrors = [
            'official' => $surveysTable->getImportErrors($officialSurveyId),
            'organization' => $surveysTable->getImportErrors($organizationSurveyId)
        ];
        $respondentsTable = TableRegistry::get('Respondents');
        $this->set([
            'titleForLayout' => $community->name . '\'s Progress in the CRI Program',
            'score' => $community->score,
            'officialSurveyOpen' => $surveysTable->isOpen($communityId, 'official'),
            'organizationSurveyOpen' => $surveysTable->isOpen($communityId, 'organization'),
            'officialUninvitedRespondents' => $respondentsTable->getUninvitedCount($officialSurveyId),
            'officialResponsesChecked' => $surveysTable->getChecked($officialSurveyId),
            'organizationResponsesChecked' => $surveysTable->getChecked($organizationSurveyId),
            'autoImportFrequency' => $surveysTable->getPerSurveyAutoImportFrequency(),
            'surveyExists' => [
                'official' => (bool)$officialSurveyId,
                'organization' => (bool)$organizationSurveyId
            ],
            'surveyIsActive' => [
                'official' => $surveysTable->surveyIsActive($officialSurveyId),
                'organization' => $surveysTable->surveyIsActive($organizationSurveyId)
            ],
            'surveyIsComplete' => [
                'official' => $surveysTable->isComplete($officialSurveyId),
                'organization' => $surveysTable->isComplete($organizationSurveyId)
            ]
        ]);
        $this->set(compact(
            'criteria',
            'importErrors',
            'purchaseUrls',
            'officialSurveyId',
            'organizationSurveyId',
            'step2SurveyPurchased',
            'step3PolicyDevPurchased'
        ));
        if ($this->Auth->user('role') == 'admin') {
            $this->prepareAdminHeader();
            $this->set(compact('community'));
        }
    }
}
