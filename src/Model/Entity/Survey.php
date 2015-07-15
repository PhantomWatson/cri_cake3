<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Survey Entity.
 */
class Survey extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'type' => true,
        'community_id' => true,
        'sm_url' => true,
        'sm_id' => true,
        'pwrrr_qid' => true,
        'production_aid' => true,
        'wholesale_aid' => true,
        'recreation_aid' => true,
        'retail_aid' => true,
        'residential_aid' => true,
        '1_aid' => true,
        '2_aid' => true,
        '3_aid' => true,
        '4_aid' => true,
        '5_aid' => true,
        'respondents_last_modified_date' => true,
        'responses_checked' => true,
        'alignment' => true,
        'alignment_passed' => true,
        'alignment_calculated' => true,
        'community' => true,
        'sm' => true,
        'respondents' => true,
        'responses' => true,
    ];
}
