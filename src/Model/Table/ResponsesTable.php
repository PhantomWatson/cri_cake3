<?php
namespace App\Model\Table;

use App\Model\Entity\Response;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
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
            ->add('alignment', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('alignment');

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
     * @param int $surveyId
     * @return int
     */
    public function getInvitedCount($surveyId)
    {
        $respondentsTable = TableRegistry::get('Respondents');
        $results = $respondentsTable->find('all')
            ->select(['id'])
            ->where([
                'survey_id' => $surveyId,
                'invited' => true
            ])
            ->contain([
                'Response' => function ($q) {
                    return $q
                        ->select(['id'])
                        ->limit(1);
                }
            ])
            ->toArray();
        $count = 0;
        foreach ($results as $respondent) {
            if (! empty($respondent['responses'])) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Retrieves SurveyMonkey responses for the specified respondents
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

            $result = $SurveyMonkey->getResponses($survey->sm_id, array_values($smRespondentIdsChunk));
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
        $conditions = ['respondent_id' => $respondentId];
        $responseRanks = $this->getResponseRanks($serializedResponse, $survey);
        foreach ($responseRanks as $sector => $rank) {
            $conditions[$sector.'_rank'] = $rank;
        }
        $count = $this->find('all')
            ->where($conditions)
            ->count();
        return $count > 0;
    }

    public function extractRespondentInfo($response)
    {
        $retval = [
            'name' => '',
            'email' => ''
        ];
        if (isset($response[0]['answers'][0]['text'])) {
            $retval['name'] = $response[0]['answers'][0]['text'];
        }
        if (isset($response[0]['answers'][3]['text'])) {
            $retval['email'] = $response[0]['answers'][3]['text'];
        }
        return $retval;
    }

    /**
     * Returns TRUE if any responses have been collected, FALSE otherwise
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
     * @param array $actualRanks ['sector_name' => rank, ...]
     * @param array $responseRanks ['sector_name' => rank, ...]
     * @return int
     */
    public function calculateAlignment($actualRanks, $responseRanks)
    {
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
     * Decodes the response and returns an array listing the ranks assigned to each sector by the respondent
     * @param string $serializedResponse
     * @param string $survey The result of a call to Survey::read()
     * @return array
     */
    public function getResponseRanks($serializedResponse, $survey)
    {
        $retval = [];

        // The question ID that covers PWRRR ranking
        $pwrrrQid = $survey['Survey']['pwrrr_qid'];

        $unserialized = unserialize(base64_decode($serializedResponse));
        foreach ($unserialized as $section) {
            if ($section['question_id'] != $pwrrrQid) {
                continue;
            }
            foreach ($section['answers'] as $answer) {
                list($sector, $rank) = $this->decodeAnswer($answer, $survey['Survey']);
                $retval[$sector] = $rank;
            }
        }
        return $retval;
    }

    /**
     * Returns array($sector, $rank) (both strings) of the sector that an answer is about and the rank the respondent gave to it.
     * @param array $answer
     * @param array $answerIds ($survey['Survey'])
     * @return array array($sector, $rank)
     */
    public function decodeAnswer($answer, $answerIds)
    {
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
}
