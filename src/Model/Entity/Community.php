<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Community Entity.
 */
class Community extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'area_id' => true,
        'public' => true,
        'fast_track' => true,
        'score' => true,
        'town_meeting_date' => true,
        'area' => true,
        'purchases' => true,
        'surveys' => true,
        'surveys_backup' => true,
        'clients' => true,
        'consultants' => true,
        'official_survey' => true,
        'organization_survey' => true
    ];

    protected function _setOfficialSurvey($survey)
    {
        $survey['type'] = 'official';
        return $survey;
    }

    protected function _setOrganizationSurvey($survey)
    {
        $survey['type'] = 'organization';
        return $survey;
    }
}
