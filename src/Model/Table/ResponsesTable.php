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
        $results = $this->Respondents->find('all')
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

        try {
            $survey = $this->Respondents->Surveys->get($surveyId);
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
}
