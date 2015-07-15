<?php
namespace App\Model\Table;

use App\Model\Entity\Survey;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Surveys Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Communities
 * @property \Cake\ORM\Association\BelongsTo $Sms
 * @property \Cake\ORM\Association\HasMany $Respondents
 * @property \Cake\ORM\Association\HasMany $Responses
 */
class SurveysTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('surveys');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Communities', [
            'foreignKey' => 'community_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Sms', [
            'foreignKey' => 'sm_id'
        ]);
        $this->hasMany('Respondents', [
            'foreignKey' => 'survey_id'
        ]);
        $this->hasMany('Responses', [
            'foreignKey' => 'survey_id'
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
            ->requirePresence('type', 'create')
            ->notEmpty('type');
            
        $validator
            ->allowEmpty('sm_url');
            
        $validator
            ->requirePresence('pwrrr_qid', 'create')
            ->notEmpty('pwrrr_qid');
            
        $validator
            ->requirePresence('production_aid', 'create')
            ->notEmpty('production_aid');
            
        $validator
            ->requirePresence('wholesale_aid', 'create')
            ->notEmpty('wholesale_aid');
            
        $validator
            ->requirePresence('recreation_aid', 'create')
            ->notEmpty('recreation_aid');
            
        $validator
            ->requirePresence('retail_aid', 'create')
            ->notEmpty('retail_aid');
            
        $validator
            ->requirePresence('residential_aid', 'create')
            ->notEmpty('residential_aid');
            
        $validator
            ->requirePresence('1_aid', 'create')
            ->notEmpty('1_aid');
            
        $validator
            ->requirePresence('2_aid', 'create')
            ->notEmpty('2_aid');
            
        $validator
            ->requirePresence('3_aid', 'create')
            ->notEmpty('3_aid');
            
        $validator
            ->requirePresence('4_aid', 'create')
            ->notEmpty('4_aid');
            
        $validator
            ->requirePresence('5_aid', 'create')
            ->notEmpty('5_aid');
            
        $validator
            ->add('respondents_last_modified_date', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('respondents_last_modified_date');
            
        $validator
            ->add('responses_checked', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('responses_checked');
            
        $validator
            ->add('alignment', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('alignment');
            
        $validator
            ->add('alignment_passed', 'valid', ['rule' => 'numeric'])
            ->requirePresence('alignment_passed', 'create')
            ->notEmpty('alignment_passed');
            
        $validator
            ->add('alignment_calculated', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('alignment_calculated');

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
        $rules->add($rules->existsIn(['community_id'], 'Communities'));
        $rules->add($rules->existsIn(['sm_id'], 'Sms'));
        return $rules;
    }
}
