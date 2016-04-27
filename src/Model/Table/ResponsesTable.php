<?php
namespace App\Model\Table;

use App\Model\Entity\Response;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
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
        $this->addBehavior('SurveyMonkey');
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
            ->add('parent_area_pwrrr_alignment', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('parent_area_pwrrr_alignment');

        $validator
            ->add('local_area_pwrrr_alignment', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('local_area_pwrrr_alignment');

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
     * @param int $surveyId
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
     * Retrieves SurveyMonkey responses for the specified respondents
     *
     * @param array $surveyId
     * @param array $smRespondentIds
     * @return array [success / fail, responses / error message]
     */
    public function getFromSurveyMonkeyForRespondents($surveyId, $smRespondentIds)
    {
        if (empty($smRespondentIds)) {
            return [true, 'No new respondents'];
        }

        $surveysTable = TableRegistry::get('Surveys');
        try {
            $survey = $surveysTable->get($surveyId);
        } catch (RecordNotFoundException $e) {
            return [false, 'Survey #'.$surveyId.' not found'];
        }

        if (! $survey->sm_id) {
            return [false, 'Survey #'.$surveyId.' has not yet been linked to SurveyMonkey'];
        }

        $SurveyMonkey = $this->getSurveyMonkeyObject();
        $retval = [];

        $smRespondentIdsSplit = array_chunk($smRespondentIds, 100);
        foreach ($smRespondentIdsSplit as $smRespondentIdsChunk) {
            /* The SurveyMonkey API limits us to 2 API requests per second and an API call was just
             * made in Respondent::getNewFromSurveyMonkey(). For extra safety, we'll delay for one second before
             * each additional API call. */
            sleep(1);

            $result = $SurveyMonkey->getResponses((string) $survey->sm_id, array_values($smRespondentIdsChunk));
            if (! $result['success']) {
                return [false, $result['message']];
            }

            $responses = $result['data'];
            foreach ($responses as $response) {
                $smRespondentId = $response['respondent_id'];
                $response = $response['questions'];
                $retval[$smRespondentId] = $response;
            }
        }

        return [true, $retval];
    }

    public function isRecorded($respondentId, $survey, $serializedResponse)
    {
        $responseRanks = $this->getResponseRanks($serializedResponse, $survey);
        if (! $responseRanks) {
            return false;
        }

        $conditions = ['respondent_id' => $respondentId];
        foreach ($responseRanks as $sector => $rank) {
            $conditions[$sector.'_rank'] = $rank;
        }
        $count = $this->find('all')
            ->where($conditions)
            ->count();
        return $count > 0;
    }

    /**
     * Returns an array with values for 'name' and 'email'
     *
     * @param array $response
     * @param int $smId
     * @return array
     */
    public function extractRespondentInfo($response, $smId)
    {
        $retval = [
            'name' => '',
            'email' => ''
        ];

        // Assume the first field contains the respondent's name
        if (isset($response[0]['answers'][0]['text'])) {
            $retval['name'] = $response[0]['answers'][0]['text'];
        }

        // Search for the first response that's a valid email address
        $surveysTable = TableRegistry::get('Surveys');
        foreach ($response as $section) {
            foreach ($section['answers'] as $answer) {
                if (! isset($answer['text'])) {
                    continue;
                }
                $answer = trim($answer['text']);
                if (Validation::email($answer)) {
                    $retval['email'] = strtolower($answer);
                    break;
                }
            }
        }

        return $retval;
    }

    /**
     * Returns TRUE if any responses have been collected, FALSE otherwise
     *
     * @param int $surveyId
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
     * @param int $surveyId
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
        $deviation_weight = [];
        foreach ($sectors as $sector) {
            $deviation_weight[$sector] = $this->getDeviationWeight($actualRanks[$sector], $responseRanks[$sector]);
        }

        // Finally, determine alignment
        $score = 0;
        foreach ($sectors as $sector) {
            $score += $sectorWeight[$sector] * $deviation_weight[$sector];
        }
        $alignment = (($score - 1) / 2) * 100;

        return $alignment;
    }

    public function getDeviationWeight($actualRank, $responseRank)
    {
        $deviation = abs($actualRank - $responseRank);
        return 1 - ($deviation * 0.2);
    }

    /**
     * Decodes the response and returns an array listing the ranks assigned to each sector by the respondent,
     * or NULL if any sector's rank is missing
     *
     * @param string $serializedResponse
     * @param Entity $survey The result of a call to SurveysTable::get()
     * @return array|null
     */
    public function getResponseRanks($serializedResponse, $survey)
    {
        $retval = [];

        // The question ID that covers PWRRR ranking
        $pwrrrQid = $survey->pwrrr_qid;

        $unserialized = unserialize(base64_decode($serializedResponse));
        foreach ($unserialized as $section) {
            if ($section['question_id'] != $pwrrrQid) {
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
     * @param array $answer
     * @param Entity $survey The result of a call to SurveysTable::get()
     * @return array [$sector, $rank]
     */
    public function decodeAnswer($answer, $survey)
    {
        $answerIds = $survey->toArray();

        $sectorId = $answer['row'];
        $sector = str_replace('_aid', '', array_search($sectorId, $answerIds));

        $rankId = $answer['col'];
        $rank = str_replace('_aid', '', array_search($rankId, $answerIds));

        return [$sector, $rank];
    }

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
     * @param int $surveyId
     * @return \Cake\ORM\Query
     */
    public function getCurrent($surveyId)
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
     * @param array $responses
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
     * @param array $choiceCounts
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
     * @param array $choiceRanks
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
     * @param array $choiceCounts
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
     * @param array $responseAverages
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
     * @param int $surveyId
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
     * @param int $surveyId
     * @return array|null
     */
    public function getInternalAlignmentPerSector($surveyId)
    {
        $responses = $this->getCurrent($surveyId);
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
     * Returns an array containing question => answer pairs for the
     * most recent response for the specified respondent
     *
     * @param string $smSurveyId
     * @param string $smRespondentId
     * @return array
     * @throws NotFoundException
     */
    public function getFullResponseFromSurveyMonkey($smSurveyId, $smRespondentId)
    {
        $SurveyMonkey = $this->getSurveyMonkeyObject();
        $result = $SurveyMonkey->getResponses((string) $smSurveyId, [$smRespondentId]);
        if (! $result['success'] || empty($result['data'])) {
            throw new NotFoundException('Could not find a response for respondent #'.$smRespondentId);
        }
        $response = $result['data'];

        // The SurveyMonkey API limits us to 2 API requests per second.
        // For extra safety, we'll delay for one second before the next API call.
        sleep(1);

        $result = $SurveyMonkey->getSurveyDetails((string) $smSurveyId);
        if (! $result['success'] || empty($result['data'])) {
            throw new NotFoundException('Could not find survey data for survey #'.$smSurveyId);
        }
        $survey = $result['data'];

        $questions = [];
        $choices = [];
        foreach ($survey['pages'] as $page) {
            foreach ($page['questions'] as $q) {
                $questions[$q['question_id']] = $q['heading'];
                foreach ($q['answers'] as $a) {
                    $choices[$a['answer_id']] = $a['text'];
                }
            }
        }
        $retval = [];
        foreach ($response[0]['questions'] as $q) {
            $questionLabel = $questions[$q['question_id']];
            $answers = [];
            foreach ($q['answers'] as $a) {
                $answer = '';
                if (isset($a['row']) && $a['row']) {
                    $answer .= $choices[$a['row']].': ';
                }
                if (isset($a['col']) && $a['col']) {
                    $answer .= $choices[$a['col']];
                }
                if (isset($a['text']) && $a['text']) {
                    $answer .= $a['text'];
                }
                $answers[] = $answer;
            }
            $retval[$questionLabel] = $answers;
        }

        return $retval;
    }
}
