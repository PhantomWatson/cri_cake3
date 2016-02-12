<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class ResponsesController extends AppController
{

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
        $responses = $this->getResponsesPage($surveyId);
        $alignmentSum = $this->getAlignmentSum($responses);
        $approvedCount = 0;
        foreach ($responses as $i => $response) {
            if ($response['respondent']['approved'] == 1) {
                $approvedCount++;
            }
        }

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
        $this->set([
            'alignmentLastSet' => $alignmentLastSet,
            'parentArea' => $parentArea,
            'communityId' => $survey->community_id,
            'communityName' => $community->name,
            'internalAlignment' => $this->Responses->getInternalAlignment($surveyId),
            'responses' => $responses,
            'sectors' => $surveysTable->getSectors(),
            'survey' => $survey,
            'surveyId' => $surveyId,
            'surveyType' => $survey->type,
            'titleForLayout' => 'View and Update Alignment',
            'totalAlignment' => $approvedCount ? round($alignmentSum / $approvedCount) : 0
        ]);
    }

    private function getResponsesPage($surveyId)
    {
        $query = $this->Responses
            ->find('all')
            ->where(['Responses.survey_id' => $surveyId])
            ->contain([
                'Respondents' => function ($q) {
                    return $q->select(['id', 'email', 'name', 'title', 'approved']);
                }
            ])
            ->order(['Responses.response_date' => 'DESC']);

        $count = $query->count();
        if ($count) {
            $query->limit($count);
        }

        $this->cookieSort('AdminResponsesView');

        $responses = $this->paginate($query);

        // Only return the most recent response for each respondent
        $retval = [];
        foreach ($responses as $i => $response) {
            $respondentId = $response['respondent']['id'];

            if (isset($retval[$respondentId]['revision_count'])) {
                $retval[$respondentId]['revision_count']++;
                continue;
            }

            $retval[$respondentId] = $response;
            $retval[$respondentId]['revision_count'] = 0;
        }

        return $retval;
    }

    private function getAlignmentSum($responses)
    {
        $alignmentSum = 0;
        foreach ($responses as $i => $response) {
            if ($response['respondent']['approved'] == 1) {
                $alignmentSum += $response->alignment;
            }
        }
        return $alignmentSum;
    }
}
