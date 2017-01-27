<?php

namespace App\Event;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;

class SurveysListener implements EventListenerInterface
{
    /**
     * implementedEvents() method
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Model.Response.afterImport' => 'updateAlignment',
            'Model.Respondent.afterUninvitedApprove' => 'updateAlignment',
            'Model.Community.afterUpdateCommunityArea' => 'updateAlignment'
        ];
    }

    /**
     * Calls SurveyTable::updateAlignment()
     *
     * @param \Cake\Event\Event $event Event
     * @param array $meta Array of metadata (surveyId, etc.)
     * @return void
     */
    public function updateAlignment(Event $event, array $meta = [])
    {
        $surveysTable = TableRegistry::get('Surveys');
        if (isset($meta['surveyId'])) {
            $surveysTable->updateAlignment($meta['surveyId']);
        } elseif (isset($meta['communityId'])) {
            $surveys = $surveysTable->find('all')
                ->select(['id'])
                ->where(['community_id' => $meta['communityId']]);
            foreach ($surveys as $survey) {
                $surveysTable->updateAlignment($survey->id);
            }
        } else {
            $msg = 'Cannot update alignment: Questionnaire ID not specified';
            throw new InternalErrorException($msg);
        }
    }
}
