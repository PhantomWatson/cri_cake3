<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Area Entity.
 */
class Area extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'fips' => true,
        'production_rank' => true,
        'wholesale_rank' => true,
        'retail_rank' => true,
        'residential_rank' => true,
        'recreation_rank' => true,
        'parent_id' => true,
        'communities' => true,
        'statistic' => true,
    ];
}
