<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Response Entity.
 */
class Response extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'respondent_id' => true,
        'survey_id' => true,
        'response' => true,
        'production_rank' => true,
        'wholesale_rank' => true,
        'retail_rank' => true,
        'residential_rank' => true,
        'recreation_rank' => true,
        'alignment_vs_local' => true,
        'alignment_vs_parent' => true,
        'response_date' => true,
        'respondent' => true,
        'survey' => true,
    ];
}
