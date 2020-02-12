<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Deliveries Model
 *
 * @property \App\Model\Table\DeliverablesTable&\Cake\ORM\Association\BelongsTo $Deliverables
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\CommunitiesTable&\Cake\ORM\Association\BelongsTo $Communities
 *
 * @method \App\Model\Entity\Delivery get($primaryKey, $options = [])
 * @method \App\Model\Entity\Delivery newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Delivery[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Delivery|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Delivery patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Delivery[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Delivery findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @method \App\Model\Entity\Delivery saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Delivery[]|\Cake\Datasource\ResultSetInterface|false saveMany($entities, $options = [])
 */
class DeliveriesTable extends Table
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

        $this->setTable('deliveries');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Deliverables', [
            'foreignKey' => 'deliverable_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Communities', [
            'foreignKey' => 'community_id',
            'joinType' => 'INNER',
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
        $rules->add($rules->existsIn(['deliverable_id'], 'Deliverables'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['community_id'], 'Communities'));

        return $rules;
    }

    /**
     * Returns a boolean indicating whether or not a community has received a specified deliverable
     *
     * @param int $communityId Community ID
     * @param int $deliverableId Deliverable ID
     * @return bool
     */
    public function isRecorded($communityId, $deliverableId)
    {
        $count = $this->find('all')
            ->select(['id'])
            ->where([
                'community_id' => $communityId,
                'deliverable_id' => $deliverableId,
            ])
            ->count();

        return $count > 0;
    }

    /**
     * Returns the most recent date of the specified delivery
     *
     * @param int $deliverableId Deliverable ID
     * @param int $communityId Community ID
     * @return \Cake\I18n\FrozenTime|null
     */
    public function getDate($deliverableId, $communityId)
    {
        /** @var \App\Model\Entity\Delivery $result Delivery entity */
        $result = $this->find('all')
            ->select(['created'])
            ->where([
                'community_id' => $communityId,
                'deliverable_id' => $deliverableId,
            ])
            ->orderDesc('created')
            ->first();

        return $result ? $result->created : null;
    }
}
