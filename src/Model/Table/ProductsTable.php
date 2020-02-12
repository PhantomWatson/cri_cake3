<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Products Model
 *
 * @property \App\Model\Table\PurchasesTable&\Cake\ORM\Association\HasMany $Purchases
 * @method \App\Model\Entity\Product get($primaryKey, $options = [])
 * @method \App\Model\Entity\Product newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Product[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Product|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Product patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Product[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Product findOrCreate($search, callable $callback = null, $options = [])
 * @method \App\Model\Entity\Product saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Product[]|\Cake\Datasource\ResultSetInterface|false saveMany($entities, $options = [])
 */
class ProductsTable extends Table
{
    public const OFFICIALS_SURVEY = 1;
    public const OFFICIALS_SUMMIT = 2;
    public const ORGANIZATIONS_SURVEY = 3;
    public const ORGANIZATIONS_SUMMIT = 4;
    public const POLICY_DEVELOPMENT = 5;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->setTable('products');
        $this->setDisplayField('description');
        $this->setPrimaryKey('id');
        $this->hasMany('Purchases', [
            'foreignKey' => 'product_id',
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
            ->requirePresence('description', 'create')
            ->notEmpty('description');

        $validator
            ->requirePresence('item_code', 'create')
            ->notEmpty('item_code');

        $validator
            ->add('price', 'valid', ['rule' => 'numeric'])
            ->requirePresence('price', 'create')
            ->notEmpty('price');

        return $validator;
    }

    /**
     * Returns whether or not this community has purchased this product
     *
     * @param int $communityId Community ID
     * @param int $productId Product ID
     * @return bool
     */
    public function isPurchased($communityId, $productId)
    {
        $purchasesTable = TableRegistry::getTableLocator()->get('Purchases');
        $count = $purchasesTable->find('all')
            ->where([
                'product_id' => $productId,
                'community_id' => $communityId,
            ])
            ->where(function ($exp, $q) {
                return $exp->isNull('refunded');
            })
            ->count();

        return $count > 0;
    }

    /**
     * Returns an array containing a status code, message, and conditionally the purchase url
     *
     * Status codes:
     *      0: Purchase not possible
     *      1: Purchase needed
     *      2: Purchased
     *
     * @param int $communityId Community ID
     * @param int $productId Product ID
     * @param int $clientId Client ID
     * @return array
     */
    public function getPurchaseStatus($communityId, $productId, $clientId)
    {
        /* Products:
         * 1: Community Leadership Alignment Assessment
         * 2: Leadership Summit
         * 3: Community Organizations Alignment Assessment
         * 4: Facilitated Community Awareness Conversation
         * 5: PWR3 Policy Development */

        // Has this been purchased?
        $purchased = $this->isPurchased($communityId, $productId);
        if ($purchased) {
            return [2, 'Purchased'];
        }

        // Is this purchase not possible because a prerequisite has not been purchased?
        $product = $this->get($productId);
        if ($product->prerequisite && ! $this->isPurchased($communityId, $product->prerequisite)) {
            $prerequisite = $this->get($product->prerequisite);

            return [0, 'Cannot purchase before purchasing ' . $prerequisite->description . '.'];
        }

        $purchaseUrl = $this->getPurchaseUrl($productId, $clientId, $communityId);

        return [1, 'Can be purchased', $purchaseUrl];
    }

    /**
     * Returns the URL for the specified purchase
     *
     * @param int $productId Product ID
     * @param int $clientId Client user ID
     * @param int $communityId Community ID
     * @return string
     */
    public function getPurchaseUrl($productId, $clientId, $communityId)
    {
        $retval = 'https://commerce.cashnet.com/';
        $retval .= Configure::read('cashNetId');
        $retval .= '?itemcnt=1';

        // Add client info
        $retval .= '&custcode=' . $clientId;
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->get($clientId);
        $nameSplit = explode(' ', $user->name);
        $retval .= '&lname=' . array_pop($nameSplit);
        $retval .= '&fname=' . implode(' ', $nameSplit);

        // Add product info
        $product = $this->get($productId);
        $retval .= '&itemcode1=EMC001-' . $product->item_code;
        $retval .= '&desc1=' . urlencode($product->description);
        $retval .= '&amount1=' . $product->price;

        // Add extra custom variables
        $retval .= '&ref1type1=community_id&ref1val1=' . $communityId;

        return $retval;
    }

    /**
     * Returns the product ID for the product with the specified CASHNet item_code
     *
     * @param string $itemCode Item code
     * @return null
     */
    public function getIdFromItemCode($itemCode)
    {
        $result = $this->find('all')
            ->select(['id'])
            ->where(['item_code' => $itemCode])
            ->first();

        return $result ? $result->id : null;
    }

    /**
     * Returns the product ID for presentation a, b, c, or d
     *
     * @param string $presentationLetter Presentation letter
     * @return int
     * @throws \Cake\Network\Exception\InternalErrorException
     */
    public function getProductIdForPresentation($presentationLetter)
    {
        $presentationLetter = strtolower($presentationLetter);
        switch ($presentationLetter) {
            case 'a':
                return $this::OFFICIALS_SURVEY;
            case 'b':
                return $this::OFFICIALS_SUMMIT;
            case 'c':
                return $this::ORGANIZATIONS_SURVEY;
            case 'd':
                return $this::ORGANIZATIONS_SUMMIT;
        }

        $msg = 'No product found for presentation "' . $presentationLetter . '"';
        throw new InternalErrorException($msg);
    }

    /**
     * Returns the lowercase presentation letter for a specified product ID, or null if not applicable
     *
     * @param int $productId Product ID
     * @return null|string
     */
    public function getPresentationLetter($productId)
    {
        switch ($productId) {
            case $this::OFFICIALS_SURVEY:
                return 'a';
            case $this::OFFICIALS_SUMMIT:
                return 'b';
            case $this::ORGANIZATIONS_SURVEY:
                return 'c';
            case $this::ORGANIZATIONS_SUMMIT:
                return 'd';
        }

        return null;
    }
}
