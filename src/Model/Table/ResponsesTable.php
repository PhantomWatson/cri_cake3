<?php
namespace App\Model\Table;

use App\Model\Entity\Survey;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Responses Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Respondents
 * @property \Cake\ORM\Association\BelongsTo $Surveys
 */
class ResponsesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('responses');
        $this->displayField('respondent_id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Respondents', [
            'foreignKey' => 'respondent_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Surveys', [
            'foreignKey' => 'survey_id',
            'joinType' => 'INNER'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('response', 'create')
            ->notEmpty('response');

        $validator
            ->add('production_rank', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('production_rank');

        $validator
            ->add('wholesale_rank', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('wholesale_rank');

        $validator
            ->add('retail_rank', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('retail_rank');

        $validator
            ->add('residential_rank', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('residential_rank');

        $validator
            ->add('recreation_rank', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('recreation_rank');

        $validator
            ->add('alignment_vs_parent', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('alignment_vs_parent');

        $validator
            ->add('alignment_vs_local', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('alignment_vs_local');

        $validator
            ->add('response_date', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('response_date');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['respondent_id'], 'Respondents'));
        $rules->add($rules->existsIn(['survey_id'], 'Surveys'));

        return $rules;
    }

    /**
     * Returns the number of invited respondents who have any responses
     * (not the number of distinct responses including repeats)
     *
     * @param int $surveyId Survey ID
     * @return int
     */
    public function getInvitedCount($surveyId)
    {
        return $this->find('all')
            ->select(['Responses.id', 'Respondents.id'])
            ->where(['Responses.survey_id' => $surveyId])
            ->matching('Respondents', function ($q) {
                return $q->where(['Respondents.invited' => 1]);
            })
            ->count();
    }

    /**
     * Returns the number of approved respondents who have any responses
     * (not the number of distinct responses including repeats)
     *
     * @param int $surveyId Survey ID
     * @return int
     */
    public function getApprovedCount($surveyId)
    {
        return $this->find('all')
            ->select(['Responses.id', 'Respondents.id'])
            ->where(['Responses.survey_id' => $surveyId])
            ->matching('Respondents', function ($q) {
                return $q->where(['Respondents.approved' => 1]);
            })
            ->count();
    }

    /**
     * Returns whether or not the given response has already been recorded
     *
     * @param int $respondentId Respondent ID
     * @param array $survey Survey array
     * @param string $serializedResponse Serialized response
     * @return bool
     */
    public function isRecorded($respondentId, $survey, $serializedResponse)
    {
        $responseRanks = $this->getResponseRanks($serializedResponse, $survey);
        if (! $responseRanks) {
            return false;
        }

        $conditions = ['respondent_id' => $respondentId];
        foreach ($responseRanks as $sector => $rank) {
            $conditions[$sector . '_rank'] = $rank;
        }
        $count = $this->find('all')
            ->where($conditions)
            ->count();

        return $count > 0;
    }

    /**
     * Returns TRUE if any responses have been collected, FALSE otherwise
     *
     * @param int $surveyId Survey ID
     * @return bool
     */
    public function responsesHaveBeenCollected($surveyId)
    {
        $count = $this->find('all')
            ->where(['survey_id' => $surveyId])
            ->count();

        return $count > 0;
    }

    /**
     * Returns a count of responses for unique respondents (not counting repeats)
     *
     * @param int $surveyId Survey ID
     * @return int
     */
    public function getDistinctCount($surveyId)
    {
        return $this->find('all')
            ->select(['respondent_id'])
            ->distinct(['respondent_id'])
            ->where(['survey_id' => $surveyId])
            ->count();
    }

    /**
     * Returns calculated alignment for an individual response
     *
     * @param array|null $actualRanks ['sector_name' => rank, ...]
     * @param array $responseRanks ['sector_name' => rank, ...]
     * @return int|null
     */
    public function calculateAlignment($actualRanks, $responseRanks)
    {
        if (! $actualRanks) {
            return null;
        }

        // Determine sector weights
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        $sectorWeight = [];
        foreach ($sectors as $sector) {
            $sectorWeight[$sector] = 1 - (0.2 * ($actualRanks[$sector] - 1)); // 1 for rank 1, 0.2 for rank 5
        }

        // Determine deviation weights
        $deviationWeight = [];
        foreach ($sectors as $sector) {
            $deviationWeight[$sector] = $this->getDeviationWeight($actualRanks[$sector], $responseRanks[$sector]);
        }

        // Finally, determine alignment
        $score = 0;
        foreach ($sectors as $sector) {
            $score += $sectorWeight[$sector] * $deviationWeight[$sector];
        }
        $alignment = (($score - 1) / 2) * 100;

        return (int)$alignment;
    }

    /**
     * Returns the deviation weight between an actual and user-provided PWRRR rank
     *
     * @param int $actualRank Actual PWRRR rank
     * @param int $responseRank PWRRR rank from response
     * @return float
     */
    public function getDeviationWeight($actualRank, $responseRank)
    {
        $deviation = abs($actualRank - $responseRank);

        return 1 - ($deviation * 0.2);
    }

    /**
     * Decodes the response and returns an array listing the ranks assigned to each sector by the respondent,
     * or NULL if any sector's rank is missing
     *
     * @param string $serializedResponse Serialized response
     * @param Entity $survey The result of a call to SurveysTable::get()
     * @return array|null
     */
    public function getResponseRanks($serializedResponse, $survey)
    {
        $retval = [];

        // The question ID that covers PWRRR ranking
        $pwrrrQid = $survey->pwrrr_qid;

        $response = unserialize(base64_decode($serializedResponse));
        foreach ($response['pages'][0]['questions'] as $section) {
            if ($section['id'] != $pwrrrQid) {
                continue;
            }
            foreach ($section['answers'] as $answer) {
                list($sector, $rank) = $this->decodeAnswer($answer, $survey);
                $retval[$sector] = $rank;
            }
        }

        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        if (count($retval) == count($sectors)) {
            return $retval;
        }

        return null;
    }

    /**
     * Returns [$sector, $rank] (both strings) of the sector that an
     * answer is about and the rank the respondent gave to it.
     *
     * @param array $answer Answer array
     * @param Entity $survey The result of a call to SurveysTable::get()
     * @return array [$sector, $rank]
     */
    public function decodeAnswer($answer, $survey)
    {
        $answerIds = $survey->toArray();

        $sectorId = $answer['row_id'];
        $sector = str_replace('_aid', '', array_search($sectorId, $answerIds));

        $rankId = $answer['choice_id'];
        $rank = str_replace('_aid', '', array_search($rankId, $answerIds));

        return [$sector, $rank];
    }

    /**
     * Returns a query for all responses for the specified survey
     *
     * @param int $surveyId Survey ID
     * @return \Cake\ORM\Query
     */
    public function getAll($surveyId)
    {
        return $this->find('all')
            ->select(['response', 'created'])
            ->where(['survey_id' => $surveyId])
            ->contain([
                'Respondent' => function ($q) {
                    return $q->select(['email', 'name', 'approved']);
                }
            ])
            ->order(['created' => 'DESC']);
    }

    /**
     * Returns all current (not overridden by more recent)
     * approved responses for the selected survey.
     *
     * @param int $surveyId Survey ID
     * @return \Cake\ORM\Query
     */
    public function getCurrentApproved($surveyId)
    {
        $surveysTable = TableRegistry::get('Surveys');
        $sectorFields = $surveysTable->getSectorFieldNames();

        $respondentsTable = TableRegistry::get('Respondents');
        $respondents = $respondentsTable->find('all')
            ->select(['id'])
            ->where([
                'survey_id' => $surveyId,
                'approved' => 1
            ])
            ->toArray();
        $respondentIds = Hash::extract($respondents, '{n}.id');

        if (empty($respondentIds)) {
            return [];
        }

        $responses = $this->find('all')
            ->where([
                'survey_id' => $surveyId,
                function ($exp, $q) use ($respondentIds) {
                    return $exp->in('respondent_id', $respondentIds);
                }
            ])
            ->order(['response_date' => 'DESC']);

        $retval = [];
        foreach ($responses as $response) {
            $rId = $response->respondent_id;
            if (isset($retval[$rId])) {
                if ($retval[$rId]['response_date'] < $response->response_date) {
                    $retval[$rId] = $response;
                }
            } else {
                $retval[$rId] = $response;
            }
        }

        return $retval;
    }

    /**
     * Returns an array with the percent of responses gave a sector a
     * particular rank, in the form $retval[$sectorName][$rank] = $percent.
     *
     * @param array $responses Responses array
     * @return array
     */
    public function getChoiceCounts($responses)
    {
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        $retval = [];
        foreach ($sectors as $sector) {
            for ($rank = 1; $rank <= 5; $rank++) {
                $choiceCount = 0;
                foreach ($responses as $response) {
                    if ($response["{$sector}_rank"] == $rank) {
                        $choiceCount++;
                    }
                }
                $retval[$sector][$rank] = $choiceCount;
            }
        }

        return $retval;
    }

    /**
     * Rank all possible sector/chosenRank combinations according to how
     * many times they were chosen and return an array in the form
     * $retval[$sectorName][$rankChoice] = $choiceRank.
     *
     * @param array $choiceCounts Array of the counts of how many times each choice was chosen
     * @return array
     */
    public function getChoiceRanks($choiceCounts)
    {
        $retval = [];
        foreach ($choiceCounts as $sector => $counts) {
            arsort($counts);
            $i = 1;
            $prevCount = null;
            $prevChoiceRank = null;
            foreach ($counts as $rankChoice => $count) {
                /* If choices have the same count, they share a choiceRank,
                 * and the next choiceRank is skipped over, causing
                 * choiceRanks like 1,1,1,4,5 if there's a
                 * three-way tie for the most-chosen rank. */
                if ($count === $prevCount) {
                    $choiceRank = $prevChoiceRank;
                } else {
                    $choiceRank = $i;
                    $prevChoiceRank = $i;
                    $prevCount = $count;
                }
                $retval[$sector][$rankChoice] = $choiceRank;
                $i++;
            }
        }

        return $retval;
    }

    /**
     * Returns the weights used in internal alignment calculations,
     * based on each how each choice ranks from most-chosen to least-chosen
     * in each sector of a particular survey.
     *
     * @param array $choiceRanks Array of ranks, indexed by sector
     * @return array
     */
    public function getChoiceWeights($choiceRanks)
    {
        $retval = [];
        foreach ($choiceRanks as $sector => $ranks) {
            foreach ($ranks as $sectorRank => $choiceRank) {
                $retval[$sector][$sectorRank] = 0.2 * $choiceRank;
            }
        }

        return $retval;
    }

    /**
     * Return an array of $sector => $averageRank
     *
     * @param array $choiceCounts Array of the counts for each choice, indexed by sector
     * @return array
     */
    public function getResponseAverages($choiceCounts)
    {
        $responseCount = 0;
        $retval = [];
        foreach ($choiceCounts as $sector => $counts) {
            if (! $responseCount) {
                $responseCount = array_sum($counts);
            }
            $sum = 0;
            foreach ($counts as $rank => $count) {
                $sum += $rank * $count;
            }
            $retval[$sector] = $sum / $responseCount;
        }

        return $retval;
    }

    /**
     * Return an array of values representing how much each rank-choice
     * deviates from the average of choices in responses for that sector,'
     * in the format $retval[$sector][$rank] = $deviation.
     *
     * @param array $responseAverages Array of the average ranks, indexed by sector
     * @return array
     */
    public function getDeviationsFromAverage($responseAverages)
    {
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        $retval = [];
        foreach ($sectors as $sector) {
            for ($rank = 1; $rank <= 5; $rank++) {
                $average = $responseAverages[$sector];
                $retval[$sector][$rank] = $rank - $average + 5;
            }
        }

        return $retval;
    }

    /**
     * Return a numerical value representing how aligned a survey's responses are
     * with each other.
     *
     * @param int $surveyId Survey ID
     * @return float
     */
    public function getInternalAlignment($surveyId)
    {
        $alignmentPerSector = $this->getInternalAlignmentPerSector($surveyId);

        return empty($alignmentPerSector) ? 0 : array_sum($alignmentPerSector);
    }

    /**
     * Return an array of sector => alignment, with 'alignment' representing
     * how aligned a survey's responses are with each other in that sector.
     *
     * @param int $surveyId Survey ID
     * @return array|null
     */
    public function getInternalAlignmentPerSector($surveyId)
    {
        $responses = $this->getCurrentApproved($surveyId);
        if (empty($responses)) {
            return null;
        }
        $choiceCounts = $this->getChoiceCounts($responses);
        $choiceRanks = $this->getChoiceRanks($choiceCounts);
        $choiceWeights = $this->getChoiceWeights($choiceRanks);
        $responseAverages = $this->getResponseAverages($choiceCounts);
        $deviations = $this->getDeviationsFromAverage($responseAverages);

        $alignmentPerSector = [];
        foreach ($choiceCounts as $sector => $counts) {
            $alignments = [];
            $responseCount = array_sum($counts);
            for ($rank = 1; $rank <= 5; $rank++) {
                $weight = $choiceWeights[$sector][$rank];
                $frequency = $counts[$rank] ? ($counts[$rank] / $responseCount) : 0;
                $deviation = $deviations[$sector][$rank];
                $alignments[] = $weight * $frequency * $deviation;
            }
            $alignmentPerSector[$sector] = array_sum($alignments);
        }

        return $alignmentPerSector;
    }

    /**
     * Decodes the response and returns a boolean indicating if the respondent is aware of
     * an existing comprehensive community plan, or NULL if either the respondent did not
     * answer that question or if that question's ID is unknown
     *
     * @param string $serializedResponse Serialized response
     * @param Survey $survey The result of a call to SurveysTable::get()
     * @return array|null
     */
    public function getAwareOfPlan($serializedResponse, Survey $survey)
    {
        if (! $survey->aware_of_plan_qid) {
            return null;
        }

        $rawResponse = unserialize(base64_decode($serializedResponse));
        $questions = isset($rawResponse['pages']) ? $rawResponse['pages'][0]['questions'] : $rawResponse;
        $affirmativeAnswerIds = [
            $survey->aware_of_city_plan_aid,
            $survey->aware_of_county_plan_aid,
            $survey->aware_of_regional_plan_aid
        ];
        $negativeAnswerId = $survey->unaware_of_plan_aid;

        foreach ($questions as $section) {
            if (isset($section['id'])) {
                $questionId = $section['id'];
            } elseif (isset($section['question_id'])) {
                $questionId = $section['question_id'];
            } else {
                continue;
            }

            if ($questionId != $survey->aware_of_plan_qid) {
                continue;
            }

            foreach ($section['answers'] as $answer) {
                if (! isset($answer['choice_id'])) {
                    continue;
                }
                if ($answer['choice_id'] == $negativeAnswerId) {
                    return false;
                }
                if (in_array($answer['choice_id'], $affirmativeAnswerIds)) {
                    return true;
                }
            }
        }

        return null;
    }

    /**
     * Returns the count of approved respondents to the specified
     * survey that gave a yes or no/skipped response to the
     * "aware of plan" question
     *
     * @param int $surveyId Survey ID
     * @param bool $aware TRUE for aware and FALSE for unaware/unknown
     * @return int
     */
    public function getApprovedAwareOfPlanCount($surveyId, $aware = true)
    {
        $query = $this->find('all')
            ->select(['respondent_id'])
            ->where(['Responses.survey_id' => $surveyId])
            ->matching('Respondents', function ($q) {
                return $q->where(['Respondents.approved' => 1]);
            });
        if ($aware) {
            $query->where(['Responses.aware_of_plan' => true]);
        } else {
            $query->where([
                'OR' => [
                    'Responses.aware_of_plan' => false,
                    function ($exp, $q) {
                        return $exp->isNull('Responses.aware_of_plan');
                    }
                ]
            ]);
        }

        // Make sure only one response per respondent is counted
        $results = $query->toArray();
        $respondentIds = Hash::extract($results, '{n}.respondent_id');
        $respondentIds = array_unique($respondentIds);

        return count($respondentIds);
    }

    /**
     * Returns the count of approved respondents to the specified survey
     * that answered that they were not aware of any comprehensive plan
     * for their community, or who did not answer that question
     *
     * @param int $surveyId Survey ID
     * @return int
     */
    public function getApprovedUnawareOfPlanCount($surveyId)
    {
        return $this->getApprovedAwareOfPlanCount($surveyId, false);
    }
}
