<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Purchase Entity.
 *
 * @property int $id
 * @property int $user_id
 * @property int $community_id
 * @property int $product_id
 * @property int $amount
 * @property string $postback
 * @property bool $admin_added
 * @property string|null $source
 * @property string $notes
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $refunded
 * @property int|null $refunder_id
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Community $community
 * @property \App\Model\Entity\Product $product
 * @property \App\Model\Entity\User|null $refunder
 * @property \App\Model\Entity\Invoice|null $invoice
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
        'amount' => true,
    ];
}
