<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class RespondentsController extends AppController
{

    public function unapproved($surveyId = null)
    {
        $surveysTable = TableRegistry::get('Surveys');
        try {
            $survey = $surveysTable->get($surveyId);
        } catch (RecordNotFoundException $e) {
            throw new NotFoundException('Sorry, we couldn\'t find a survey with that ID (#'.$surveyId.').');
        }

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($survey->community_id);

        $this->set([
            'titleForLayout' => $community->name.' Uninvited '.ucwords($survey->type).' Survey Respondents',
            'respondents' => [
                'unaddressed' => $this->Respondents->getUnaddressedUnapproved($surveyId),
                'dismissed' => $this->Respondents->getDismissed($surveyId)
            ],
            'survey_id' => $surveyId
        ]);
        $this->render('unapproved');
    }
}
