<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Respondent Entity.
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
        'survey_id' => true,
        'sm_respondent_id' => true,
        'invited' => true,
        'approved' => true,
        'response_date' => true,
        'survey' => true,
        'sm_respondent' => true,
        'responses' => true,
    ];
}
