<?php
namespace App\Model\Table;

use App\Model\Entity\Product;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Products Model
 *
 * @property \Cake\ORM\Association\HasMany $Purchases
 */
class ProductsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('products');
        $this->displayField('description');
        $this->primaryKey('id');
        $this->hasMany('Purchases', [
            'foreignKey' => 'product_id'
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
        $purchasesTable = TableRegistry::get('Purchases');
        $count = $purchasesTable->find('all')
            ->where([
                'product_id' => $productId,
                'community_id' => $communityId
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

        // Is this purchase not possible because the community isn't in the correct CRI step?
        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($communityId);
        $product = $this->get($productId);
        if ($community->score < $product->step) {
            return [0, 'Cannot purchase before beginning Step ' . $product->step . '.'];
        }

        // Is this purchase not possible because a prerequisite has not been purchased?
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
        $usersTable = TableRegistry::get('Users');
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
            ->first()
            ->toArray();

        return isset($result['id']) ? $result['id'] : null;
    }
}
