<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class RespondentsController extends AppController
{

    public function unapproved($surveyId = null)
    {
        $surveysTable = TableRegistry::get('Surveys');
        if ($surveyId) {
            try {
                $survey = $surveysTable->get($surveyId);
            } catch (RecordNotFoundException $e) {
                throw new NotFoundException('Sorry, we couldn\'t find a questionnaire with that ID (#'.$surveyId.').');
            }
        } else {
            throw new NotFoundException('Questionnaire ID not specified.');
        }

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($survey->community_id);

        $this->set([
            'community' => $community,
            'respondents' => [
                'unaddressed' => $this->Respondents->getUnaddressedUnapproved($surveyId),
                'dismissed' => $this->Respondents->getDismissed($surveyId)
            ],
            'survey' => $survey,
            'titleForLayout' => $community->name.' Uninvited '.ucwords($survey->type).' Questionnaire Respondents'
        ]);
        $this->render('/Client/Respondents/unapproved');
    }

    public function approveUninvited($respondentId)
    {
        $respondent = $this->Respondents->get($respondentId);
        $respondent->approved = 1;
        $this->set([
            'success' => (boolean) $this->Respondents->save($respondent)
        ]);
        $this->viewBuilder()->layout('blank');
        $this->render(DS.'Client'.DS.'Respondents'.DS.'approve_uninvited');
    }

    public function dismissUninvited($respondentId)
    {
        $respondent = $this->Respondents->get($respondentId);
        $respondent->approved = -1;
        $this->set([
            'success' => (boolean) $this->Respondents->save($respondent)
        ]);
        $this->viewBuilder()->layout('blank');
        $this->render(DS.'Client'.DS.'Respondents'.DS.'dismiss_uninvited');
    }
}
