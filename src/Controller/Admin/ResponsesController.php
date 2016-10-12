<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
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

        // Process update
        if ($this->request->is('post') || $this->request->is('put')) {
            $survey = $surveysTable->patchEntity($survey, $this->request->data);
            $errors = $survey->errors();
            if (empty($errors) && $surveysTable->save($survey)) {
                $this->Flash->success('Alignment set');
                $survey->alignment_calculated = $survey->modified;
                $surveysTable->save($survey);
            } else {
                $this->Flash->error('There was an error updating this questionnaire');
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
            $order = 0;
            $previousAvg = null;
            foreach ($averageRanks as $sector => $avg) {
                if ($avg != $previousAvg) {
                    $order++;
                }
                $rankOrder[$sector] = $order;
                $previousAvg = $avg;
            }
        } else {
            $rankOrder = null;
        }

        $this->prepareAdminHeader();
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
            'titleForLayout' => $community->name . ': View and Update Alignment'
        ]);
    }

    /**
     * Returns a string to use as a CSS class for styling the
     * total internal alignment for a questionnaire
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

    /**
     * Looks for responses with missing local_area_pwrrr_alignment and
     * parent_area_pwrrr_alignment values and populates them.
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
                        return $exp->isNull('local_area_pwrrr_alignment');
                    },
                    function ($exp, $q) {
                        return $exp->isNull('parent_area_pwrrr_alignment');
                    }
                ]
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
                $fieldName = "{$scope}_area_id";
                $areaId = $community->$fieldName;
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
                    'recreation' => $response->recreation_rank
                ];
                $alignment = $this->Responses->calculateAlignment($actualRanks, $responseRanks);

                $fieldName = "{$scope}_area_pwrrr_alignment";
                $response->$fieldName = $alignment;
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
     * @param int $respondentId
     * @return void
     * @throws InternalErrorException
     */
    public function getFullResponse($respondentId)
    {
        $respondentsTable = TableRegistry::get('Respondents');
        $respondent = $respondentsTable->get($respondentId);

        if ($respondent->sm_respondent_id) {
            $smRespondentId = $respondent->sm_respondent_id;

        // If sm_respondent_id is not set, retrieve it
        } else {
            $smRespondentId = $respondentsTable->getSmRespondentId($respondentId);

            // The SurveyMonkey API limits us to 2 API requests per second.
            // For extra safety, we'll delay for one second before the next API call.
            sleep(1);

            // And save it in this respondent's DB record
            $respondent = $respondentsTable->patchEntity($respondent, [
                'sm_respondent_id' => $smRespondentId
            ]);
            if ($respondent->errors()) {
                $msg = 'There was an error saving the respondent\'s sm_respondent_id (' . $smRespondentId . ')';
                throw new InternalErrorException($msg);
            }
            $respondentsTable->save($respondent);
        }

        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($respondent->survey_id);
        $fullResponse = $this->Responses->getFullResponseFromSurveyMonkey($survey->sm_id, $smRespondentId);
        $this->set('response', $fullResponse);
        $this->set('_serialize', ['response']);
    }
}
