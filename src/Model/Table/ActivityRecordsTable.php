<?php
namespace App\Model\Table;

use App\Model\Entity\ActivityRecord;
use Cake\I18n\Time;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;

/**
 * ActivityRecords Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Communities
 * @property \Cake\ORM\Association\BelongsTo $Surveys
 *
 * @method ActivityRecord get($primaryKey, $options = [])
 * @method ActivityRecord newEntity($data = null, array $options = [])
 * @method ActivityRecord[] newEntities(array $data, array $options = [])
 * @method ActivityRecord|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method ActivityRecord patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method ActivityRecord[] patchEntities($entities, array $data, array $options = [])
 * @method ActivityRecord findOrCreate($search, callable $callback = null, $options = [])
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

        $this->setTable('activity_records');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

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

        // Remember some entity names in case the entity is deleted in the future
        if (isset($meta['communityId']) && ! isset($meta['communityName'])) {
            $communitiesTable = TableRegistry::get('Communities');
            $community = $communitiesTable->get($meta['communityId']);
            $meta['communityName'] = $community->name;
        }
        if (isset($meta['userId'])) {
            $usersTable = TableRegistry::get('Users');
            $user = $usersTable->get($meta['userId']);
            if (! isset($meta['userName'])) {
                $meta['userName'] = $user->name;
            }
            if (! isset($meta['userRole'])) {
                $meta['userRole'] = $user->role;
            }
        }

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
        if ($activityRecord->getErrors() || ! $this->save($activityRecord)) {
            $msg = 'There was an error adding a record to the activity log';
            if ($activityRecord->getErrors()) {
                $msg .= ': ' . print_r($activityRecord->getErrors(), true);
            }
            throw new InternalErrorException($msg);
        }
    }

    /**
     * Returns the first date of this survey getting activated, or NULL if no record is found
     *
     * @param int $surveyId Survey ID
     * @return Time|null
     */
    public function getSurveyActivationDate($surveyId)
    {
        /** @var ActivityRecord $result */
        $result = $this->find('all')
            ->select(['created'])
            ->where([
                'survey_id' => $surveyId,
                'event' => 'Model.Survey.afterActivate'
            ])
            ->order(['created' => 'ASC'])
            ->first();

        return $result ? $result->created : null;
    }

    /**
     * Returns the most recent activity record for the specified community
     *
     * @param int $communityId Community ID
     * @return ActivityRecord|null
     */
    public function getMostRecentForCommunity($communityId)
    {
        /** @var ActivityRecord $result */
        $result = $this->find()
            ->select([
                'event',
                'created'
            ])
            ->where(['community_id' => $communityId])
            ->order(['ActivityRecords.created' => 'DESC'])
            ->first();

        return $result;
    }
}
