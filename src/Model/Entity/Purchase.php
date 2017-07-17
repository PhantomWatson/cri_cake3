<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Purchase Entity.
 */
class Purchase extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'user_id' => true,
        'community_id' => true,
        'product_id' => true,
        'admin_added' => true,
        'postback' => true,
        'source' => true,
        'notes' => true,
        'refunded' => true,
        'refunder_id' => true,
        'user' => true,
        'community' => true,
        'product' => true,
        'refunder' => true,
        'amount' => true
    ];
}
