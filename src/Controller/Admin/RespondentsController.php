<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\Respondent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validation;

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
     * @param int $respondentId Respondent ID
     * @return void
     */
    public function approveUninvited($respondentId)
    {
        $respondent = $this->Respondents->get($respondentId);
        $respondent->approved = 1;
        $result = $this->Respondents->save($respondent);
        if ($result) {
            $this->dispatchUninvitedEvent(true, $respondent);
        }
        $this->set([
            'success' => (bool)$result
        ]);
        $this->viewBuilder()->setLayout('blank');
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
        $result = $this->Respondents->save($respondent);
        if ($result) {
            $this->dispatchUninvitedEvent(false, $respondent);
        }
        $this->set([
            'success' => (bool)$result
        ]);
        $this->viewBuilder()->setLayout('blank');
        $this->render(DS . 'Client' . DS . 'Respondents' . DS . 'dismiss_uninvited');
    }

    /**
     * Dispatches an event for uninvited respondent approval or dismissal
     *
     * @param bool $approved True for approved and false for dismissed
     * @param Respondent $respondent Respondent entity
     * @return void
     */
    private function dispatchUninvitedEvent($approved, $respondent)
    {
        $surveyId = $respondent->survey_id;
        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($surveyId);
        $eventName = 'Model.Respondent.afterUninvited' . ($approved ? 'Approve' : 'Dismiss');
        $event = new Event($eventName, $this, ['meta' => [
            'communityId' => $survey->community_id,
            'surveyId' => $surveyId,
            'respondentId' => $respondent->id,
            'respondentName' => $respondent->name,
            'surveyType' => $survey->type
        ]]);
        $this->eventManager()->dispatch($event);
    }

    /**
     * View method
     *
     * @param int $surveyId Survey ID
     * @return \Cake\Http\Response
     */
    public function view($surveyId)
    {
        $this->loadComponent('SurveyResults');
        $this->SurveyResults->prepareRespondentsClientsPage(compact('surveyId'));

        return $this->render('/Client/Respondents/index');
    }

    /**
     * Shows a list of all invalid email addresses in respondents table
     *
     * @return void
     */
    public function validateEmails()
    {
        $respondents = $this->Respondents->find('all')
            ->select(['id', 'email']);
        $invalidEmails = [];
        foreach ($respondents as $respondent) {
            if (! Validation::email($respondent->email)) {
                $invalidEmails[] = $respondent->email;
            }
        }

        $this->set(compact('invalidEmails'));
    }
}
