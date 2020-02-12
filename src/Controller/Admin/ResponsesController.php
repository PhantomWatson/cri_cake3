<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\SurveyMonkey\SurveyMonkey;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\NotFoundException;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;

class ResponsesController extends AppController
{
    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('SurveyProcessing');
        $this->loadComponent('RequestHandler');
    }

    /**
     * View method
     *
     * @param int|null $surveyId Survey ID
     * @return void
     */
    public function view($surveyId = null)
    {
        $surveysTable = TableRegistry::get('Surveys');

        if ($surveyId) {
            try {
                $survey = $surveysTable->get($surveyId);
            } catch (RecordNotFoundException $e) {
                $msg = 'Sorry, we couldn\'t find a questionnaire in the database with that ID number.';
                throw new NotFoundException($msg);
            }
        } else {
            throw new NotFoundException('Questionnaire ID not specified.');
        }

        $responses = $this->SurveyProcessing->getCurrentResponses($surveyId);

        if ($surveysTable->newResponsesHaveBeenReceived($surveyId)) {
            $this->Flash->set('New responses have been received since this community\'s alignment was last set.');
        }

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($survey->community_id, [
            'contain' => ['LocalAreas', 'ParentAreas'],
        ]);

        $internalAlignment = $this->Responses->getInternalAlignmentPerSector($surveyId);
        $internalAlignmentSum = empty($internalAlignment) ? 0 : array_sum($internalAlignment);

        // Get averages
        if ($responses) {
            $ranks = [];
            $sectors = $surveysTable->getSectors();
            foreach ($responses as $response) {
                foreach ($sectors as $sector) {
                    $ranks[$sector][] = $response[$sector . '_rank'];
                }
            }
            $averageRanks = [];
            foreach ($sectors as $sector) {
                $avg = array_sum($ranks[$sector]) / count($responses);
                $averageRanks[$sector] = round($avg, 1);
            }
            asort($averageRanks);
        } else {
            $sectors = null;
            $averageRanks = null;
        }

        // Determine the order of those sorted averages
        if ($responses) {
            $rankOrder = [];
            $order = 1;
            $previousAvg = null;
            $previousOrder = null;
            foreach ($averageRanks as $sector => $avg) {
                /* If two sectors have the same average, they share an order (e.g. 1st),
                 * and the next order (e.g. 2nd) is skipped over, causing
                 * orders like 1,1,1,4,5 if there's a
                 * three-way tie for the most-chosen rank. */
                if ($avg === $previousAvg) {
                    $rankOrder[$sector] = $previousOrder;
                } else {
                    $rankOrder[$sector] = $order;
                    $previousOrder = $order;
                }

                $previousAvg = $avg;
                $order++;
            }
        } else {
            $rankOrder = null;
        }

        // Get total unaddressed-unapproved responses
        $unaddressedUnapprovedCount = 0;
        foreach ($responses as $response) {
            if ($response->respondent->approved == 0) {
                $unaddressedUnapprovedCount++;
            }
        }

        $this->set([
            'averageRanks' => $averageRanks,
            'community' => $community,
            'internalAlignment' => $internalAlignment,
            'internalAlignmentClass' => $this->getInternalAlignmentClass($internalAlignmentSum, $community),
            'internalAlignmentSum' => $internalAlignmentSum,
            'rankOrder' => $rankOrder,
            'responses' => $responses,
            'sectors' => $sectors,
            'survey' => $survey,
            'titleForLayout' => $community->name . ': Community ' . ucwords($survey->type) . 's Alignment',
            'unaddressedUnapprovedCount' => $unaddressedUnapprovedCount,
        ]);
    }

    /**
     * Returns a string to use as a CSS class for styling the
     * total internal alignment for a questionnaire
     *
     * @param float $sum Sum of internal alignments
     * @param \App\Model\Entity\Community $community Community
     * @return string
     */
    private function getInternalAlignmentClass($sum, $community)
    {
        $adjustedScore = $sum - $community->intAlignmentAdjustment;

        // Green if adjusted alignment is more aligned (smaller number) than the acceptable threshold
        if ($adjustedScore < (-1 * $community->intAlignmentThreshold)) {
            return 'aligned-well';
        }

        // Yellow if its alignment falls within the acceptable threshold
        if (abs($adjustedScore) <= $community->intAlignmentThreshold) {
            return 'aligned-acceptably';
        }

        // Red if its alignment is worse (greater than) the acceptable threshold
        return 'aligned-poorly';
    }

    /**
     * Looks for responses with missing alignment_vs_local and
     * alignment_vs_parent values and populates them
     *
     * @return void
     */
    public function calculateMissingAlignments()
    {
        $this->Flash->set('Searching for missing alignments');
        $responses = $this->Responses->find('all')
            ->where([
                'OR' => [
                    function ($exp, $q) {
                        return $exp->isNull('alignment_vs_local');
                    },
                    function ($exp, $q) {
                        return $exp->isNull('alignment_vs_parent');
                    },
                ],
            ])
            ->all();
        if ($responses->isEmpty()) {
            $this->Flash->set('No missing alignments');

            return;
        }

        $this->Flash->set(count($responses) . ' response(s) with missing alignments found');

        $missingAreaReports = [];
        foreach ($responses as $response) {
            $surveysTable = TableRegistry::get('Surveys');
            $survey = $surveysTable->get($response->survey_id);

            // Determine actual ranks for alignment calculation
            $communitiesTable = TableRegistry::get('Communities');
            $community = $communitiesTable->get($survey->community_id);
            $areasTable = TableRegistry::get('Areas');
            foreach (['local', 'parent'] as $scope) {
                $areaId = $community->{"{$scope}_area_id"};
                if (! $areaId) {
                    if (! isset($missingAreaReports[$survey->community_id][$scope])) {
                        $msg = 'Community #' . $survey->community_id . ' has no ' . $scope . ' area';
                        $this->Flash->error($msg);
                        $missingAreaReports[$survey->community_id][$scope] = true;
                    }
                    continue;
                }
                $actualRanks = $areasTable->getPwrrrRanks($areaId);

                // Needs replaced
                $responseRanks = [
                    'production' => $response->production_rank,
                    'wholesale' => $response->wholesale_rank,
                    'retail' => $response->retail_rank,
                    'residential' => $response->residential_rank,
                    'recreation' => $response->recreation_rank,
                ];
                $alignment = $this->Responses->calculateAlignment($actualRanks, $responseRanks);

                $response->{"alignment_vs_{$scope}"} = $alignment;
            }

            if ($this->Responses->save($response)) {
                $this->Flash->success('Response #' . $response->id . ' updated');
            }
        }
    }

    /**
     * Returns a JSON object containing 'response', containing
     * question => answer pairs for the most recent response
     * for the specified respondent. Also retrieves and sets
     * $respondent->sm_respondent_id if it was not already set
     *
     * @param int $respondentId Respondent ID
     * @return void
     * @throws \Cake\Network\Exception\InternalErrorException
     */
    public function getFullResponse($respondentId)
    {
        $respondentsTable = TableRegistry::get('Respondents');
        $respondent = $respondentsTable->get($respondentId);
        $SurveyMonkey = new SurveyMonkey();

        if ($respondent->sm_respondent_id) {
            $smRespondentId = $respondent->sm_respondent_id;

        // If sm_respondent_id is not set, retrieve it
        } else {
            $smRespondentId = $SurveyMonkey->getSmRespondentId($respondentId);

            // And save it in this respondent's DB record
            $respondent = $respondentsTable->patchEntity($respondent, [
                'sm_respondent_id' => $smRespondentId,
            ]);
            if ($respondent->getErrors()) {
                $msg = 'There was an error saving the respondent\'s sm_respondent_id (' . $smRespondentId . ')';
                throw new InternalErrorException($msg);
            }
            $respondentsTable->save($respondent);
        }

        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($respondent->survey_id);
        try {
            $fullResponse = $SurveyMonkey->getFullResponse($survey->sm_id, $smRespondentId);
            $this->set('response', $fullResponse);
            $this->set('_serialize', ['response']);
        } catch (NotFoundException $e) {
            $this->response->withStatus(404);
            $this->set('message', $e->getMessage());
            $this->set('_serialize', ['message']);
        }
    }
}
