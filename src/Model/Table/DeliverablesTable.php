<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Deliverables Model
 *
 * @property \App\Model\Table\DeliveriesTable|\Cake\ORM\Association\HasMany $Deliveries
 *
 * @method \App\Model\Entity\Deliverable get($primaryKey, $options = [])
 * @method \App\Model\Entity\Deliverable newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Deliverable[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Deliverable|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Deliverable patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Deliverable[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Deliverable findOrCreate($search, callable $callback = null, $options = [])
 */
class DeliverablesTable extends Table
{
    const PRESENTATION_A_MATERIALS = 1;
    const PRESENTATION_B_MATERIALS = 2;
    const PRESENTATION_C_MATERIALS = 3;
    const PRESENTATION_D_MATERIALS = 4;
    const POLICY_DEVELOPMENT = 5;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('deliverables');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->hasMany('Deliveries', [
            'foreignKey' => 'deliverable_id'
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
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->requirePresence('delivered_by', 'create')
            ->notEmpty('delivered_by');

        $validator
            ->requirePresence('delivered_to', 'create')
            ->notEmpty('delivered_to');

        return $validator;
    }
}
