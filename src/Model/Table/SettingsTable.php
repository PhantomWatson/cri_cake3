<?php
namespace App\Model\Table;

use App\Model\Entity\Setting;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Settings Model
 *
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

        $this->table('settings');
        $this->displayField('name');
        $this->primaryKey('id');

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
    public function getIntAlignmentThreshhold()
    {
        $result = $this->find('all')
            ->select(['value'])
            ->where(['name' => 'intAlignmentThreshhold'])
            ->first();
        if (empty($result)) {
            throw new NotFoundException('intAlignmentThreshhold setting not found');
        }
        return $result->value;
    }
}
