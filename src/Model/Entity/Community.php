<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Community Entity.
 *
 * @property int $id
 * @property string $name
 * @property int|null $local_area_id
 * @property int|null $parent_area_id
 * @property bool $public
 * @property bool $fast_track
 * @property float $score
 * @property \Cake\I18n\FrozenDate|null $town_meeting_date
 * @property float $intAlignmentAdjustment
 * @property float $intAlignmentThreshold
 * @property \Cake\I18n\FrozenDate|null $presentation_a
 * @property \Cake\I18n\FrozenDate|null $presentation_b
 * @property \Cake\I18n\FrozenDate|null $presentation_c
 * @property \Cake\I18n\FrozenDate|null $presentation_d
 * @property bool $dummy
 * @property string $notes
 * @property bool $active
 * @property string|null $slug
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \App\Model\Entity\Area|null $local_area
 * @property \App\Model\Entity\Area|null $parent_area
 * @property \App\Model\Entity\Purchase[] $purchases
 * @property \App\Model\Entity\OptOut[] $opt_outs
 * @property \App\Model\Entity\Survey[] $surveys
 * @property \Cake\ORM\Entity[] $surveys_backup
 * @property \App\Model\Entity\Survey|null $official_survey
 * @property \App\Model\Entity\Survey|null $organization_survey
 * @property \App\Model\Entity\User[] $clients
 * @property \App\Model\Entity\ActivityRecord[] $activity_records
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
        'slug' => true,
    ];

    /**
     * Automatically set the 'type' field for OfficialSurvey entities
     *
     * @param \Cake\ORM\Entity $survey Survey entity
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
     * @param \Cake\ORM\Entity $survey Survey entity
     * @return mixed
     */
    protected function _setOrganizationSurvey($survey)
    {
        $survey['type'] = 'organization';

        return $survey;
    }
}
