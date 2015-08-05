<?php
namespace App\Model\Table;

use App\Model\Entity\Respondent;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Respondents Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Surveys
 * @property \Cake\ORM\Association\BelongsTo $SmRespondents
 * @property \Cake\ORM\Association\HasMany $Responses
 */
class RespondentsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('respondents');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Surveys', [
            'foreignKey' => 'survey_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('SmRespondents', [
            'foreignKey' => 'sm_respondent_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('Responses', [
            'foreignKey' => 'respondent_id'
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
            ->add('email', 'valid', ['rule' => 'email'])
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->add('invited', 'valid', ['rule' => 'boolean'])
            ->requirePresence('invited', 'create')
            ->notEmpty('invited');

        $validator
            ->add('approved', 'valid', ['rule' => 'numeric'])
            ->requirePresence('approved', 'create')
            ->notEmpty('approved');

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
        $rules->add($rules->isUnique(['email']));
        $rules->add($rules->existsIn(['survey_id'], 'Surveys'));
        $rules->add($rules->existsIn(['sm_respondent_id'], 'SmRespondents'));
        return $rules;
    }

    public function getList($surveyId, $invited = null)
    {
        $conditions = ['survey_id' => $surveyId];
        if (isset($invited)) {
            $conditions['invited'] = $invited;
        }
        return $this->find('list')
            ->where($conditions)
            ->toArray();
    }

    public function getInvitedList($surveyId)
    {
        return $this->getList($surveyId, true);
    }

    public function getUninvitedList($surveyId)
    {
        return $this->getList($surveyId, false);
    }
}
