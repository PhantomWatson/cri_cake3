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

        $communitiesTable = TableRegistry::get('Communities');
        $parentAreaId = $communitiesTable->getParentAreaId($survey->community_id);
        $parentArea = $areasTable->get($parentAreaId);
        $totalAlignment = 0;
        $responses = $this->SurveyProcessing->getResponsesPage($surveyId);
        $alignmentSum = $this->SurveyProcessing->getAlignmentSum($responses);
        $approvedCount = $this->SurveyProcessing->getApprovedCount($responses);

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

        if ($survey->alignment_calculated) {
            $timestamp = strtotime($survey->alignment_calculated);
            $alignmentLastSet = date('F j', $timestamp).'<sup>'.date('S', $timestamp).'</sup>'.date(', Y', $timestamp);
        } else {
            $alignmentLastSet = null;
        }

        $community = $communitiesTable->get($survey->community_id);
        $totalAlignment = $approvedCount ? round($alignmentSum / $approvedCount) : 0;
        $internalAlignment = $this->Responses->getInternalAlignment($surveyId);
        $this->set([
            'alignmentLastSet' => $alignmentLastSet,
            'community' => $community,
            'internalAlignment' => $internalAlignment,
            'parentArea' => $parentArea,
            'responses' => $responses,
            'sectors' => $surveysTable->getSectors(),
            'survey' => $survey,
            'titleForLayout' => 'View and Update Alignment',
            'totalAlignment' => $totalAlignment
        ]);
    }
}
