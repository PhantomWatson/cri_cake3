<?php
namespace App\Model\Table;

use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;

/**
 * ActivityRecords Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Communities
 * @property \Cake\ORM\Association\BelongsTo $Surveys
 *
 * @method \App\Model\Entity\ActivityRecord get($primaryKey, $options = [])
 * @method \App\Model\Entity\ActivityRecord newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\ActivityRecord[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ActivityRecord|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ActivityRecord patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ActivityRecord[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\ActivityRecord findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ActivityRecordsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('activity_records');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
        $this->belongsTo('Communities', [
            'foreignKey' => 'community_id'
        ]);
        $this->belongsTo('Surveys', [
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
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('event', 'create')
            ->notEmpty('event');

        $validator
            ->allowEmpty('meta');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['community_id'], 'Communities'));
        $rules->add($rules->existsIn(['survey_id'], 'Surveys'));

        return $rules;
    }

    /**
     * Adds an activity
     *
     * @param string $eventName Event name
     * @param array $meta Variables relevant to this record
     * @return void
     * @throws \Cake\Network\Exception\InternalErrorException
     */
    public function add($eventName, $meta = [])
    {
        $recordData = [
            'event' => $eventName
        ];

        // Move some variables from $meta to their own fields
        $extractedVars = ['userId', 'communityId', 'surveyId'];
        foreach ($extractedVars as $var) {
            if (isset($meta[$var])) {
                $field = Inflector::underscore($var);
                $recordData[$field] = $meta[$var];
                unset($meta[$var]);
            }
        }

        $recordData['meta'] = serialize($meta);

        $activityRecord = $this->newEntity($recordData);
        if ($activityRecord->errors()) {
            throw new InternalErrorException('There was an error adding a record to the activity log');
        }
        debug($activityRecord);
        if ($this->save($activityRecord)) {
            echo 'saved!';
        } else {
            echo 'OH NO NOT SAVED';
        }

        debug($activityRecord->errors());
    }
}
