<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * StatCategory Entity.
 */
class StatCategory extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'statistic' => true,
    ];
}
