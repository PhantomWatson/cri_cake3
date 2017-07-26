<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Area Entity.
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int $fips
 * @property int $production_rank
 * @property int $wholesale_rank
 * @property int $retail_rank
 * @property int $residential_rank
 * @property int $recreation_rank
 * @property int $parent_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
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
        'type' => true,
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
