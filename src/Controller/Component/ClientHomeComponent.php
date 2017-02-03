<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;

class ClientHomeComponent extends Component
{
    public $components = ['Flash', 'Auth'];

    /**
     * Sets up the client home view for the selected community
     *
     * @param int $communityId Community ID
     * @return bool
     */
    public function prepareClientHome($communityId)
    {
        if (! $communityId) {
            return false;
        }

        $communitiesTable = TableRegistry::get('Communities');
        try {
            $community = $communitiesTable->get($communityId);
        } catch (RecordNotFoundException $e) {
            return false;
        }

        $purchaseUrls = [];
        $productsTable = TableRegistry::get('Products');
        $userId = $this->Auth->user('id');
        for ($productId = 1; $productId <= 5; $productId++) {
            $purchaseUrls[$productId] = $productsTable->getPurchaseUrl($productId, $userId, $communityId);
        }

        $respondentsTable = TableRegistry::get('Respondents');
        $surveysTable = TableRegistry::get('Surveys');
        $officialSurveyId = $surveysTable->getSurveyId($communityId, 'official');
        $organizationSurveyId = $surveysTable->getSurveyId($communityId, 'organization');
        $criteria = $communitiesTable->getProgress($communityId);
        $this->_registry->getController()->set([
            'autoImportFrequency' => $surveysTable->getPerSurveyAutoImportFrequency(),
            'importErrors' => [
                'official' => $surveysTable->getImportErrors($officialSurveyId),
                'organization' => $surveysTable->getImportErrors($organizationSurveyId)
            ],
            'officialResponsesChecked' => $surveysTable->getChecked($officialSurveyId),
            'officialUninvitedRespondents' => $respondentsTable->getUninvitedCount($officialSurveyId),
            'organizationResponsesChecked' => $surveysTable->getChecked($organizationSurveyId),
            'score' => $community->score,
            'step2SurveyPurchased' => $criteria[2]['survey_purchased'],
            'step3PolicyDevPurchased' => $criteria[3]['policy_dev_purchased'],
            'surveyExists' => [
                'official' => (bool)$officialSurveyId,
                'organization' => (bool)$organizationSurveyId
            ],
            'surveyIsActive' => [
                'official' => $surveysTable->isActive($officialSurveyId),
                'organization' => $surveysTable->isActive($organizationSurveyId)
            ],
            'surveyIsComplete' => [
                'official' => $surveysTable->isComplete($officialSurveyId),
                'organization' => $surveysTable->isComplete($organizationSurveyId)
            ],
            'titleForLayout' => $community->name . '\'s Progress in the CRI Program'
        ]);
        $this->_registry->getController()->set(compact(
            'community',
            'criteria',
            'purchaseUrls',
            'officialSurveyId',
            'organizationSurveyId'
        ));

        return true;
    }
}
