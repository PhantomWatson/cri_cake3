<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Response Entity.
 *
 * @property int $id
 * @property int $respondent_id
 * @property int $survey_id
 * @property string $response
 * @property int|null $production_rank
 * @property int|null $wholesale_rank
 * @property int|null $retail_rank
 * @property int|null $residential_rank
 * @property int|null $recreation_rank
 * @property int|null $alignment_vs_local
 * @property int|null $alignment_vs_parent
 * @property bool|null $aware_of_plan
 * @property \Cake\I18n\FrozenTime|null $response_date
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \App\Model\Entity\Respondent $respondent
 * @property \App\Model\Entity\Survey $survey
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
        'aware_of_plan' => true,
        'response_date' => true,
        'respondent' => true,
        'survey' => true,
    ];
}
