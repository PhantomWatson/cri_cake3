<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Statistic Entity.
 *
 * @property int $id
 * @property int $area_id
 * @property int $stat_category_id
 * @property float $value
 * @property int|null $year
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \App\Model\Entity\Area $area
 * @property \App\Model\Entity\StatCategory $stat_category
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
