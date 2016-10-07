<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class RespondentsController extends AppController
{
    /**
     * Unapproved method
     *
     * @param int|null $surveyId Survey ID
     * @return void
     */
    public function unapproved($surveyId = null)
    {
        $surveysTable = TableRegistry::get('Surveys');
        if ($surveyId) {
            try {
                $survey = $surveysTable->get($surveyId);
            } catch (RecordNotFoundException $e) {
                $msg = 'Sorry, we couldn\'t find a questionnaire with that ID (#' . $surveyId . ').';
                throw new NotFoundException($msg);
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
            'titleForLayout' => $community->name . ' Uninvited ' . ucwords($survey->type) . ' Questionnaire Respondents'
        ]);
        $this->render('/Client/Respondents/unapproved');
    }

    /**
     * Approve uninvited respondents method
     *
     * @param $respondentId
     * @return void
     */
    public function approveUninvited($respondentId)
    {
        $respondent = $this->Respondents->get($respondentId);
        $respondent->approved = 1;
        $this->set([
            'success' => (bool)$this->Respondents->save($respondent)
        ]);
        $this->viewBuilder()->layout('blank');
        $this->render(DS . 'Client' . DS . 'Respondents' . DS . 'approve_uninvited');
    }

    /**
     * Dismiss uninvited respondents method
     *
     * @param int $respondentId Respondent ID
     * @return void
     */
    public function dismissUninvited($respondentId)
    {
        $respondent = $this->Respondents->get($respondentId);
        $respondent->approved = -1;
        $this->set([
            'success' => (bool)$this->Respondents->save($respondent)
        ]);
        $this->viewBuilder()->layout('blank');
        $this->render(DS . 'Client' . DS . 'Respondents' . DS . 'dismiss_uninvited');
    }
}
