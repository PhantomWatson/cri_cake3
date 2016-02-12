<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CommunityArea Entity.
 *
 * @property int $id
 * @property int $community_id
 * @property \App\Model\Entity\Community $community
 * @property int $area_id
 * @property \App\Model\Entity\Area $area
 * @property \Cake\I18n\Time $created
 */
class CommunityArea extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];
}
