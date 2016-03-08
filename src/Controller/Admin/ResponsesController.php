<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class ResponsesController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('SurveyProcessing');
    }

    public function view($surveyId = null)
    {
        $surveysTable = TableRegistry::get('Surveys');
        $areasTable = TableRegistry::get('Areas');

        if ($surveyId) {
            try {
                $survey = $surveysTable->get($surveyId);
            } catch (RecordNotFoundException $e) {
                throw new NotFoundException('Sorry, we couldn\'t find a survey in the database with that ID number.');
            }
        } else {
            throw new NotFoundException('Survey ID not specified.');
        }

        $responses = $this->SurveyProcessing->getResponsesPage($surveyId);

        // Process update
        if ($this->request->is('post') || $this->request->is('put')) {
            $survey = $surveysTable->patchEntity($survey, $this->request->data);
            $errors = $survey->errors();
            if (empty($errors) && $surveysTable->save($survey)) {
                $this->Flash->success('Alignment set');
                $survey->alignment_calculated = $survey->modified;
                $surveysTable->save($survey);
            } else {
                $this->Flash->error('There was an error updating this survey');
            }
        }

        if ($surveysTable->newResponsesHaveBeenReceived($surveyId)) {
            $this->Flash->set('New responses have been received since this community\'s alignment was last set.');
        }

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($survey->community_id, [
            'contain' => ['LocalAreas', 'ParentAreas']
        ]);

        $internalAlignment = $this->Responses->getInternalAlignmentPerSector($surveyId);
        $internalAlignmentSum = empty($internalAlignment) ? 0 : array_sum($internalAlignment);

        $this->set([
            'community' => $community,
            'internalAlignment' => $internalAlignment,
            'internalAlignmentClass' => $this->getInternalAlignmentClass($internalAlignmentSum, $community),
            'internalAlignmentSum' => $internalAlignmentSum,
            'responses' => $responses,
            'sectors' => $surveysTable->getSectors(),
            'survey' => $survey,
            'titleForLayout' => 'View and Update Alignment'
        ]);
    }

    /**
     * Returns a string to use as a CSS class for styling the
     * total internal alignment for a survey
     *
     * @param float $sum
     * @param Community $community
     * @return string
     */
    private function getInternalAlignmentClass($sum, $community)
    {
        $adjustedScore = $sum - $community->intAlignmentAdjustment;

        // Green if adjusted alignment is more aligned (smaller number) than the acceptable threshhold
        if ($adjustedScore < (-1 * $community->intAlignmentThreshhold)) {
            return 'aligned-well';
        }

        // Yellow if its alignment falls within the acceptable threshhold
        if (abs($adjustedScore) <= $community->intAlignmentThreshhold) {
            return 'aligned-acceptably';
        }

        // Red if its alignment is worse (greater than) the acceptable threshhold
        return 'aligned-poorly';
    }
}
