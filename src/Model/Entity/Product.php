<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Product Entity.
 *
 * @property int $id
 * @property string $description
 * @property string $item_code
 * @property int $price
 * @property int $step
 * @property int|null $prerequisite
 * @property \App\Model\Entity\Purchase[] $purchases
 */
class Product extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'description' => true,
        'item_code' => true,
        'price' => true,
        'purchases' => true,
    ];
}
