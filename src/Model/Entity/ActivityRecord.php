<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ActivityRecord Entity
 *
 * @property int $id
 * @property string $event
 * @property int|null $user_id
 * @property int|null $community_id
 * @property int|null $survey_id
 * @property string|null $meta
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\User|null $user
 * @property \App\Model\Entity\Community|null $community
 * @property \App\Model\Entity\Survey|null $survey
 */
class ActivityRecord extends Entity
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
