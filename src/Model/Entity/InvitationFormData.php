<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * InvitationFormData Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $survey_id
 * @property string $data
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Survey $survey
 */
class InvitationFormData extends Entity
{

}
