<?php
namespace App\Model\Table;

use App\Model\Entity\Product;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
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

    public function isPurchased($communityId, $productId)
    {
        $purchasesTable = TableRegistry::get('Purchases');
        $count = $purchasesTable->find('all')
            ->where([
                'product_id' => $productId,
                'community_id' => $communityId,
                'refunded' => null
            ])
            ->count();
        return $count > 0;
    }

    /**
     * Returns an array containing a status code, message, and conditionally the purchase url
     * Status codes:
     *      0: Purchase not possible
     *      1: Purchase needed
     *      2: Purchased
     * @param int $communityId
     * @param int $productId
     * @return array
     */
    public function getPurchaseStatus($communityId, $productId, $clientId)
    {
        /* Products:
         * 1: Community Leadership Alignment Assessment
         * 2: Leadership Summit
         * 3: Community Alignment Assessment
         * 4: Facilitated Community Awareness Conversation
         * 5: PWR3 Policy Development */

        // Has this been purchased?
        $purchased = $this->isPurchased($communityId, $productId);
        if ($purchased) {
            return [2, 'Purchased'];
        }

        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($communityId);

        // Is this purchase not necessary because the community is on the fast track?
        if ($community->fast_track) {
            switch ($productId) {
                case 3:
                    // Community Alignment Assessment
                case 4:
                    // Facilitated Community Awareness Conversation
                case 5:
                    // PWR3 Policy Development
                    return [0, 'Purchase not necessary on Fast Track'];
            }
        }

        // Is this purchase not possible because the community hasn't passed an alignment test?
        $surveysTable = TableRegistry::get('Surveys');
        if ($productId > 1) {
            $offSurveyId = $surveysTable->getSurveyId($communityId, 'official');
            $offSurvey = $surveysTable->get($offSurveyId);
            switch ($offSurvey->alignment_passed) {
                case 0:
                    return [0, 'Community has not completed Step 2 yet.'];
                case -1:
                    if ($productId == 2) {
                        $purchaseUrl = $this->getPurchaseUrl($productId, $clientId, $communityId);
                        return [1, 'Can purchase (community is misaligned)', $purchaseUrl];
                    }
                    return [0, 'Community has not completed Step 2 yet.'];
            }
            if (! $community->fast_track && $productId > 3) {
                $orgSurveyId = $surveysTable->getSurveyId($communityId, 'organization');
                $orgSurvey = $surveysTable->get($orgSurveyId);
                switch ($orgSurvey->alignment_passed) {
                    case 0:
                        return [0, 'Community has not completed Step 3 yet.'];
                    case -1:
                        if ($productId == 4) {
                            $purchaseUrl = $this->getPurchaseUrl($productId, $clientId, $communityId);
                            return [1, 'Can purchase (community is misaligned)', $purchaseUrl];
                        }
                        return [0, 'Community has not completed Step 3 yet.'];
                }
                if ($orgSurvey->alignment_passed < 1 && $productId == 5) {
                    return [0, 'Community has not completed Step 3 yet.'];
                }
            }
        }

        // Is this half-setp product not ready to purchase or not necessary?
        if ($productId == 2 || $productId == 4) {     // Leadership Summit || Facilitated Community Awareness Conversation
            $surveyType = $productId == 2 ? 'official' : 'organization';
            $surveyId = $surveysTable->getSurveyId($communityId, $surveyType);
            $survey = $surveysTable->get($surveyId);
            switch ($survey->alignment_passed) {
                case 0:
                    return [0, 'Your assessment results have not yet been analyzed.'];
                case 1:
                    return [0, 'Not necessary'];
            }
        }

        // Is this purchase not possible because the community isn't at the correct stage?
        if (($productId == 2 && $community->score < 2)      // Leadership Summit
            || ($productId == 3 && $community->score < 2)   // Community Alignment Assessment
            || ($productId == 4 && $community->score < 3)   // Facilitated Community Awareness Conversation
        ) {
            return [0, 'Community not yet ready to make purchase'];
        }

        $purchaseUrl = $this->getPurchaseUrl($productId, $clientId, $communityId);
        return [1, 'Can be purchased', $purchaseUrl];
    }

    public function getPurchaseUrl($productId, $clientId, $communityId)
    {
        $retval = 'https://commerce.cashnet.com/';
        $retval .= (Configure::read('debug') == 0) ? 'BALL_EMC001' : 'BALL_EMC001_TEST';
        $retval .= '?itemcnt=1';

        // Add client info
        $retval .= '&custcode='.$clientId;
        $usersTable = TableRegistry::get('Users');
        $user = $usersTable->get($clientId);
        $nameSplit = explode(' ', $user->name);
        $retval .= '&lname='.array_pop($nameSplit);
        $retval .= '&fname='.implode(' ', $nameSplit);

        // Add product info
        $product = $this->get($productId);
        $retval .= '&itemcode1=EMC001-'.$this->item_code;
        $retval .= '&desc1='.urlencode($this->description);
        $retval .= '&amount1='.$this->price;

        // Add extra custom variables
        $retval .= '&ref1type1=community_id&ref1val1='.$communityId;

        return $retval;
    }

    public function getIdFromItemCode($item_code)
    {
        $result = $this->find('all')
            ->select(['id'])
            ->where(['item_code' => $item_code])
            ->first()
            ->toArray();
        return isset($result['id']) ? $result['id'] : null;
    }
}
