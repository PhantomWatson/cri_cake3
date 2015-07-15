<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Statistic Entity.
 */
class Statistic extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'area_id' => true,
        'stat_category_id' => true,
        'value' => true,
        'year' => true,
        'area' => true,
        'stat_category' => true,
    ];
}
