<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Respondent Entity.
 *
 * @property int $id
 * @property string $email
 * @property string $name
 * @property string $title
 * @property int $survey_id
 * @property string $sm_respondent_id
 * @property bool $invited
 * @property int $approved
 * @property \Cake\I18n\FrozenTime|null $response_date
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \App\Model\Entity\Survey $survey
 * @property \App\Model\Entity\Response[] $responses
 */
class Respondent extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'email' => true,
        'name' => true,
        'title' => true,
        'survey_id' => true,
        'sm_respondent_id' => true,
        'invited' => true,
        'approved' => true,
        'response_date' => true,
        'survey' => true,
        'responses' => true,
    ];
}
