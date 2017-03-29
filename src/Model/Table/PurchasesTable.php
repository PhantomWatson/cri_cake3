<?php
namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Purchases Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Communities
 * @property \Cake\ORM\Association\BelongsTo $Products
 * @property \Cake\ORM\Association\BelongsTo $Refunders
 */
class PurchasesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('purchases');
        $this->displayField('product_id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Communities', [
            'foreignKey' => 'community_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Refunders', [
            'className' => 'App\Model\Table\UsersTable',
            'foreignKey' => 'refunder_id'
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
            ->notEmpty('source', 'create');

        $validator
            ->requirePresence('postback', 'create')
            ->allowEmpty('postback');

        $validator
            ->add('refunded', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('refunded');

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
        $rules->add($rules->existsIn(['product_id'], 'Products'));
        $rules->add($rules->existsIn(['refunder_id'], 'Refunders'));

        return $rules;
    }

    /**
     * Returns an array of all purchases associated with a community
     *
     * @param int $communityId Community ID
     * @return array
     */
    public function getAllForCommunity($communityId)
    {
        return $this->find('all')
            ->where(['community_id' => $communityId])
            ->order(['Purchases.created' => 'ASC'])
            ->contain([
                'Products' => function ($q) {
                    return $q->select(['description', 'price']);
                },
                'Users' => function ($q) {
                    return $q->select(['name', 'email']);
                }
            ])
            ->toArray();
    }

    /**
     * Returns an array of the accepted values for Payments.source as keys, and their displayed labels as values
     *
     * @return array
     */
    public function getSourceOptions()
    {
        return [
            'ocra' => 'OCRA',
            'bsu' => 'Ball State University',
            'self' => 'Paid for by client community'
        ];
    }

    /**
     * Finds OCRA-funded purchases
     *
     * @param \Cake\ORM\Query $query Query
     * @param array $options Options array
     * @return \Cake\ORM\Query
     */
    public function findOcra(\Cake\ORM\Query $query, array $options)
    {
        return $query
            ->where(['Purchases.source' => 'ocra'])
            ->contain(['Communities', 'Products'])
            ->order(['Purchases.created' => 'DESC']);
    }
}
