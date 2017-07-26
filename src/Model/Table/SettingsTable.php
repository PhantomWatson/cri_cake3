<?php
namespace App\Model\Table;

use Cake\Network\Exception\NotFoundException;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Settings Model
 *
 *
 * @method \App\Model\Entity\Setting get($primaryKey, $options = [])
 * @method \App\Model\Entity\Setting newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Setting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Setting|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Setting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Setting[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Setting findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SettingsTable extends Table
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

        $this->setTable('settings');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
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
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->requirePresence('value', 'create')
            ->notEmpty('value');

        return $validator;
    }

    /**
     * @return float
     * @throws NotFoundException
     */
    public function getIntAlignmentAdjustment()
    {
        $result = $this->find('all')
            ->select(['value'])
            ->where(['name' => 'intAlignmentAdjustment'])
            ->first();
        if (empty($result)) {
            throw new NotFoundException('intAlignmentAdjustment setting not found');
        }

        return $result->value;
    }

    /**
     * @return float
     * @throws NotFoundException
     */
    public function getIntAlignmentThreshold()
    {
        $result = $this->find('all')
            ->select(['value'])
            ->where(['name' => 'intAlignmentThreshold'])
            ->first();
        if (empty($result)) {
            throw new NotFoundException('intAlignmentThreshold setting not found');
        }

        return $result->value;
    }
}
