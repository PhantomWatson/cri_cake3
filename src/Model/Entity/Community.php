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
        'local_area_id' => true,
        'parent_area_id' => true,
        'public' => true,
        'score' => true,
        'local_area' => true,
        'parent_area' => true,
        'purchases' => true,
        'surveys' => true,
        'surveys_backup' => true,
        'clients' => true,
        'consultants' => true,
        'official_survey' => true,
        'organization_survey' => true,
        'presentation_a' => true,
        'presentation_b' => true,
        'presentation_c' => true,
        'presentation_d' => true,
        'notes' => true,
        'active' => true,
        'intAlignmentAdjustment' => true,
        'intAlignmentThreshold' => true,
        'slug' => true
    ];

    /**
     * Automatically set the 'type' field for OfficialSurvey entities
     *
     * @param Entity $survey Survey entity
     * @return mixed
     */
    protected function _setOfficialSurvey($survey)
    {
        $survey['type'] = 'official';

        return $survey;
    }

    /**
     * Automatically set the 'type' field for OrganizationSurvey entities
     *
     * @param Entity $survey Survey entity
     * @return mixed
     */
    protected function _setOrganizationSurvey($survey)
    {
        $survey['type'] = 'organization';

        return $survey;
    }
}
