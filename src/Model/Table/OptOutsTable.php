<?php
namespace App\Model\Table;

use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * OptOuts Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Communities
 * @property \Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\OptOut get($primaryKey, $options = [])
 * @method \App\Model\Entity\OptOut newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OptOut[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OptOut|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OptOut patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OptOut[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OptOut findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OptOutsTable extends Table
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

        $this->setTable('opt_outs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['community_id'], 'Communities'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));

        return $rules;
    }

    /**
     * Creates a new opt-out record
     *
     * @param array $params Parameters
     * @return bool
     * @throws InternalErrorException
     */
    public function addOptOut(array $params)
    {
        if (isset($params['product_id'])) {
            $productId = $params['product_id'];
        } elseif (isset($params['presentation_letter'])) {
            $productsTable = TableRegistry::get('Products');
            $productId = $productsTable->getProductIdForPresentation($params['presentation_letter']);
        } else {
            throw new InternalErrorException('Could not opt out (missing product ID)');
        }

        $existingOptOut = $this->find('all')
            ->where([
                'user_id' => $params['user_id'],
                'community_id' => $params['community_id'],
                'product_id' => $productId
            ])
            ->count();

        // Avoid adding redundant opt-outs
        if ($existingOptOut) {
            return true;
        }

        $optOut = $this->newEntity([
            'user_id' => $params['user_id'],
            'community_id' => $params['community_id'],
            'product_id' => $productId
        ]);

        return (bool)$this->save($optOut);
    }

    /**
     * Returns an array of all product IDs that this community has opted out of
     *
     * @param int $communityId Community ID
     * @return array
     */
    public function getOptOuts($communityId)
    {
        $results = $this->find('all')
            ->select(['product_id'])
            ->where(['community_id' => $communityId])
            ->toArray();

        return Hash::extract($results, '{n}.product_id');
    }
}
