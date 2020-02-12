<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Deliverables Model
 *
 * @property \App\Model\Table\DeliveriesTable&\Cake\ORM\Association\HasMany $Deliveries
 *
 * @method \App\Model\Entity\Deliverable get($primaryKey, $options = [])
 * @method \App\Model\Entity\Deliverable newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Deliverable[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Deliverable|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Deliverable patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Deliverable[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Deliverable findOrCreate($search, callable $callback = null, $options = [])
 * @method \App\Model\Entity\Deliverable saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Deliverable[]|\Cake\Datasource\ResultSetInterface|false saveMany($entities, $options = [])
 */
class DeliverablesTable extends Table
{
    public const PRESENTATION_A_MATERIALS = 1;
    public const PRESENTATION_B_MATERIALS = 2;
    public const PRESENTATION_C_MATERIALS = 3;
    public const PRESENTATION_D_MATERIALS = 4;
    public const POLICY_DEVELOPMENT = 5;

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
            'foreignKey' => 'deliverable_id',
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

    /**
     * Returns whether or not the deliverable ID corresponds to a presentation
     *
     * @param int $deliverableId Deliverable ID
     * @return bool
     */
    public function isPresentation($deliverableId)
    {
        $presentations = [
            self::PRESENTATION_A_MATERIALS,
            self::PRESENTATION_B_MATERIALS,
            self::PRESENTATION_C_MATERIALS,
            self::PRESENTATION_D_MATERIALS,
        ];

        return in_array($deliverableId, $presentations);
    }

    /**
     * Returns the presentation letter associated with the specified deliverable ID
     *
     * @param int $deliverableId Deliverable ID
     * @return string
     * @throws \Cake\Network\Exception\InternalErrorException
     */
    public function getPresentationLetter($deliverableId)
    {
        $presentations = [
            self::PRESENTATION_A_MATERIALS => 'a',
            self::PRESENTATION_B_MATERIALS => 'b',
            self::PRESENTATION_C_MATERIALS => 'c',
            self::PRESENTATION_D_MATERIALS => 'd',
        ];

        if (array_key_exists($deliverableId, $presentations)) {
            return $presentations[$deliverableId];
        }

        throw new InternalErrorException('No presentation is associated with deliverable #' . $deliverableId);
    }
}
