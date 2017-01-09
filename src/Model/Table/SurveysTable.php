<?php
namespace App\Model\Table;

use App\SurveyMonkey\SurveyMonkey;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\Time;
use Cake\Network\Exception\InternalErrorException;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Surveys Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Communities
 * @property \Cake\ORM\Association\BelongsTo $Sms
 * @property \Cake\ORM\Association\HasMany $Respondents
 * @property \Cake\ORM\Association\HasMany $Responses
 */
class SurveysTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('surveys');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Communities', [
            'foreignKey' => 'community_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('Respondents', [
            'foreignKey' => 'survey_id'
        ]);
        $this->hasMany('Responses', [
            'foreignKey' => 'survey_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('type', 'create')
            ->notEmpty('type');

        $validator
            ->requirePresence('sm_url', 'create')
            ->notEmpty('sm_url');

        $validator
            ->requirePresence('sm_id', 'create')
            ->notEmpty('sm_id')
            ->add('sm_id', 'unique', [
                'rule' => 'validateUnique',
                'provider' => 'table',
                'message' => 'Sorry, the selected questionnaire has already been linked to a community.'
            ]);

        $validator
            ->requirePresence('pwrrr_qid', 'create')
            ->notEmpty('pwrrr_qid');

        $validator
            ->requirePresence('production_aid', 'create')
            ->notEmpty('production_aid');

        $validator
            ->requirePresence('wholesale_aid', 'create')
            ->notEmpty('wholesale_aid');

        $validator
            ->requirePresence('recreation_aid', 'create')
            ->notEmpty('recreation_aid');

        $validator
            ->requirePresence('retail_aid', 'create')
            ->notEmpty('retail_aid');

        $validator
            ->requirePresence('residential_aid', 'create')
            ->notEmpty('residential_aid');

        $validator
            ->requirePresence('1_aid', 'create')
            ->notEmpty('1_aid');

        $validator
            ->requirePresence('2_aid', 'create')
            ->notEmpty('2_aid');

        $validator
            ->requirePresence('3_aid', 'create')
            ->notEmpty('3_aid');

        $validator
            ->requirePresence('4_aid', 'create')
            ->notEmpty('4_aid');

        $validator
            ->requirePresence('5_aid', 'create')
            ->notEmpty('5_aid');

        $validator
            ->add('respondents_last_modified_date', 'valid', ['rule' => 'datetime'])
            ->notEmpty('respondents_last_modified_date');

        $validator
            ->add('responses_checked', 'valid', ['rule' => 'datetime'])
            ->notEmpty('responses_checked');

        $validator
            ->add('alignment_vs_local', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('alignment_vs_local');

        $validator
            ->add('alignment_vs_parent', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('alignment_vs_parent');

        $validator
            ->add('alignment_calculated_date', 'valid', ['rule' => 'datetime'])
            ->notEmpty('alignment_calculated_date');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['community_id'], 'Communities'));

        return $rules;
    }

    /**
     * Returns the community_id for the first survey that matches $conditions
     * @param array $conditions Query conditions
     * @return int|null
     */
    public function getCommunityId($conditions)
    {
        $results = $this->find('all')
            ->select(['community_id'])
            ->where($conditions)
            ->limit(1);

        return $results->isEmpty() ? null : $results->first()->community_id;
    }

    /**
     * Returns an array of invited_respondent_count, uninvited_respondent_count, and percent_invited_responded for a single survey type or an array of both
     * @param int $communityId Community ID
     * @param string $surveyType Survey type (optional)
     * @return array
     */
    public function getStatus($communityId, $surveyType = null)
    {
        $allTypes = ['official', 'organization'];
        if ($surveyType) {
            if (! in_array($surveyType, $allTypes)) {
                throw new InternalErrorException('Unrecognized questionnaire type: ' . $surveyType);
            }
            $types = [$surveyType];
        } else {
            $types = $allTypes;
        }

        $respondentsTable = TableRegistry::get('Respondents');
        $responsesTable = TableRegistry::get('Responses');
        $surveyStatus = [];

        foreach ($types as $type) {
            $survey = $this->find('all')
                ->select(['id', 'responses_checked'])
                ->where([
                    'community_id' => $communityId,
                    'type' => $type
                ])
                ->first();
            $invitedRespondentCount = $respondentsTable->getInvitedCount($survey->id);
            $uninvitedRespondentCount = $respondentsTable->getUninvitedCount($survey->id);
            $invitedResponseCount = $responsesTable->getInvitedCount($survey->id);
            $percentInvitedResponded = empty($invitedRespondentCount)
                ? 0
                : $invitedResponseCount / $invitedRespondentCount;
            $surveyStatus[$type] = [
                'invited_respondent_count' => $invitedRespondentCount,
                'uninvited_respondent_count' => $uninvitedRespondentCount,
                'percent_invited_responded' => round($percentInvitedResponded * 100),
                'responses_checked' => strtotime($survey->responses_checked),
                'survey_id' => $survey->id
            ];
        }
        if ($surveyType) {
            return array_pop($surveyStatus);
        }

        return $surveyStatus;
    }

    /**
     * @param int $communityId Community ID
     * @param string $type Survey type
     * @return int|null
     */
    public function getSurveyId($communityId, $type)
    {
        $results = $this->find('all')
            ->select(['id'])
            ->where([
                'community_id' => $communityId,
                'type' => $type
            ])
            ->limit(1);

        return $results->isEmpty() ? null : $results->first()->id;
    }

    /**
     * @param int $surveyId Survey ID
     * @return bool
     */
    public function setChecked($surveyId)
    {
        $survey = $this->get($surveyId);
        $survey->responses_checked = date('Y-m-d H:i:s');

        return (bool)$this->save($survey);
    }

    /**
     * @param int $surveyId Survey ID
     * @return DateTime|null
     */
    public function getChecked($surveyId)
    {
        if ($surveyId) {
            return $this->get($surveyId)->responses_checked;
        }

        return null;
    }

    /**
     * Returns the ID of the least-recently-imported survey
     * @return null|int
     */
    public function getIdForAutomatedImport()
    {
        $results = $this->find('all')
            ->select(['id'])
            ->order(['responses_checked' => 'ASC'])
            ->limit(1);

        return $results->isEmpty() ? null : $results->first()->id;
    }

    /**
     * @return array
     */
    public function getQnaIdFieldNames()
    {
        return [
            'pwrrr_qid',
            'production_aid',
            'wholesale_aid',
            'recreation_aid',
            'retail_aid',
            'residential_aid',
            '1_aid',
            '2_aid',
            '3_aid',
            '4_aid',
            '5_aid'
        ];
    }

    /**
     * Sets the SurveyMonkey Q&A IDs associated with the PWRRR-ranking question and its set of answers
     *
     * @param string $smId SurveyMonkey survey ID
     * @return array [boolean success/failure indicator, result message]
     */
    public function setQuestionAndAnswerIds($smId)
    {
        $results = $this->find('all')
            ->select(['id'])
            ->where(['sm_id' => $smId])
            ->limit(1);
        if ($results->isEmpty()) {
            return [false, "Error: No questionnaire has been recorded with SurveyMonkey id '$smId'."];
        }
        $survey = $results->first();
        $SurveyMonkey = new SurveyMonkey();
        $data = $SurveyMonkey->getPwrrrQuestionAndAnswerIds($smId)[2];
        $this->patchEntity($survey, $data);
        if ($this->save($survey)) {
            return [true, 'Question and answer IDs saved.'];
        }

        return [false, 'Error: Could not save question and answer IDs'];
    }

    /**
     * Returns the percent (0-100) of invited respondents who have responded
     * @param int $surveyId Survey ID
     * @return int
     */
    public function getInvitedResponsePercentage($surveyId)
    {
        $respondentsTable = TableRegistry::get('Respondents');
        $invitations = $respondentsTable->getInvitedCount($surveyId);
        $responsesTable = TableRegistry::get('Responses');
        $responses = $responsesTable->getInvitedCount($surveyId);
        if (! $invitations || ! $responses) {
            return 0;
        }

        return round(($responses / $invitations) * 100);
    }

    /**
     * @param int $communityId Community ID
     * @param string $surveyType Survey type
     * @return bool
     */
    public function hasBeenCreated($communityId, $surveyType)
    {
        $count = $this->find('all')
            ->where([
                'community_id' => $communityId,
                'type' => $surveyType
            ])
            ->where(function ($exp, $q) {
                return $exp->notEq('sm_url', '');
            })
            ->count();

        return $count > 0;
    }

    /**
     * @param int $surveyId Survey ID
     * @return bool
     */
    public function hasUninvitedResponses($surveyId)
    {
        $respondentsTable = TableRegistry::get('Respondents');
        $count = $respondentsTable->find('all')
            ->where([
                'survey_id' => $surveyId,
                'invited' => 0
            ])
            ->count();

        return $count > 0;
    }

    /**
     * @param int $surveyId Survey ID
     * @return bool
     */
    public function hasUnaddressedUnapprovedRespondents($surveyId)
    {
        $respondentsTable = TableRegistry::get('Respondents');
        $count = $respondentsTable->find('all')
            ->where([
                'survey_id' => $surveyId,
                'approved' => 0
            ])
            ->count();

        return $count > 0;
    }

    /**
     * @return array
     */
    public function getSectors()
    {
        return [
            'production',
            'wholesale',
            'retail',
            'residential',
            'recreation'
        ];
    }

    /**
     * Return an array of the database field names for the PWRRR sectors
     *
     * @return array
     */
    public function getSectorFieldNames()
    {
        $sectors = $this->getSectors();
        $getFieldName = function ($sector) {
            return $sector . '_rank';
        };

        return array_map($getFieldName, $sectors);
    }

    /**
     * Returns true if responses have been received since alignment was last set by an admin
     *
     * @param int $surveyId Survey ID
     * @return bool
     */
    public function newResponsesHaveBeenReceived($surveyId)
    {
        try {
            $survey = $this->get($surveyId);
        } catch (RecordNotFoundException $e) {
            return false;
        }
        $responsesTable = TableRegistry::get('Responses');
        $count = $responsesTable->find('all')
            ->where([
                'survey_id' => $surveyId,
                'created >' => $survey->alignment_calculated_date
            ])
            ->count();

        return $count > 0;
    }

    /**
     * Finds surveys can have their responses automatically imported,
     * sorted with the surveys whose responses have been least-recently
     * imported first.
     *
     * @param Query $query Query
     * @param array $options Query options
     * @return Query
     */
    public function findAutoImportCandidate(Query $query, array $options)
    {
        return $query
            ->select(['id'])
            ->where([
                function ($exp, $q) {
                    return $exp->isNotNull('Surveys.sm_id');
                },
                'active' => 1,
                'OR' => [
                    [
                        'Surveys.type' => 'official',
                        function ($exp, $q) {
                            return $exp->gt('Communities.score', '1');
                        },
                        function ($exp, $q) {
                            return $exp->lt('Communities.score', '3');
                        }
                    ],
                    [
                        'Surveys.type' => 'organization',
                        function ($exp, $q) {
                            return $exp->gt('Communities.score', '2');
                        },
                        function ($exp, $q) {
                            return $exp->lt('Communities.score', '4');
                        }
                    ]
                ]
            ])
            ->join([
                'table' => 'communities',
                'alias' => 'Communities',
                'type' => 'LEFT',
                'conditions' => [
                    'Communities.id = Surveys.community_id'
                ]
            ])
            ->order(['responses_checked' => 'ASC']);
    }

    /**
     * Returns the ID of the least-recently-checked survey that is eligible for automatic
     * response imports, or NULL if there are no eligible surveys.
     *
     * @return int|null
     */
    public function getNextAutoImportCandidate()
    {
        $results = $this->find('autoImportCandidate');

        return $results->isEmpty() ? null : $results->first()->id;
    }

    /**
     * Returns how many surveys can currently be auto-imported
     *
     * @return int
     */
    public function getAutoImportEligibleCount()
    {
        return $this->find('autoImportCandidate')->count();
    }

    /**
     * Returns a string describing how frequently an auto-import-eligible survey gets its responses automatically imported.
     * Returns a blank string if no automatic imports are taking place.
     * NOTE: $interval must be updated whenever the CRON job's frequency is changed.
     * Example output:
     *      Every 2 days                    Days are left off if < 2
     *      Every 30 hours and 15 minutes   Minutes are left off if < 10
     *      Every 30 minutes
     *
     * @return string
     */
    public function getPerSurveyAutoImportFrequency()
    {
        // Length of time in seconds between each auto-import cron job
        $siteInterval = 3 * 60; // 3 minutes

        $count = $this->getAutoImportEligibleCount();

        if (! $count) {
            return '';
        }

        $individualInterval = $count * $siteInterval;

        $days = floor($individualInterval / (60 * 60 * 24));
        if ($days >= 2) {
            return "every $days days";
        }

        $hours = floor($individualInterval / (60 * 60));
        $minutesDivisor = $individualInterval % (60 * 60);
        $minutes = floor($minutesDivisor / 60);

        if ($hours > 0) {
            $msg = ($hours == 1) ? 'every hour' : "every $hours hours";
            if ($minutes >= 10) {
                return "$msg and $minutes minutes";
            }

            return $msg;
        }

        $minutes = max($minutes, 1);

        return ($minutes == 1) ? 'every minute' : "every $minutes minutes";
    }

    /**
     * Returns an array of the most recent import errors
     * for the selected survey
     *
     * @param int $surveyId Survey ID
     * @return array|null
     */
    public function getImportErrors($surveyId)
    {
        $results = $this->find('all')
            ->select(['Surveys.import_errors'])
            ->where(['id' => $surveyId])
            ->limit(1);
        if ($results->isEmpty()) {
            return null;
        }
        $errors = $results->first()->import_errors;

        return $errors ? unserialize($errors) : null;
    }

    /**
     * Returns true if a survey exists and is active, false otherwise
     *
     * @param int $surveyId Survey ID
     * @return bool
     */
    public function isActive($surveyId)
    {
        $results = $this->find('all')
            ->select(['active'])
            ->where(['id' => $surveyId])
            ->limit(1);

        return $results->isEmpty() ? false : (bool)$results->first()->active;
    }

    /**
     * Returns whether or not any responses have been received
     *
     * @param int $surveyId SurveyID
     * @return bool
     */
    public function hasResponses($surveyId)
    {
        $respondentsTable = TableRegistry::get('Respondents');
        $count = $respondentsTable->find('all')
            ->where([
                'survey_id' => $surveyId
            ])
            ->count();

        return $count > 0;
    }

    /**
     * Returns whether or not the survey has concluded (has received responses and been deactivated)
     *
     * @param int $surveyId Survey ID
     * @return bool
     */
    public function isComplete($surveyId)
    {
        return ! $this->isActive($surveyId) && $this->hasResponses($surveyId);
    }

    /**
     * Recalculates and sets Surveys.alignment_vs_local and alignment_vs_parent
     *
     * @param int $surveyId Survey ID
     * @return void
     * @throws NotFoundException
     * @throws InternalErrorException
     */
    public function updateAlignment($surveyId)
    {
        $survey = $this->get($surveyId);

        $communityId = $this->getCommunityId(['id' => $surveyId]);
        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->find('all')
            ->select(['id', 'local_area_id', 'parent_area_id'])
            ->where(['id' => $communityId])
            ->first();
        if (!$community) {
            $msg = "No community associated with Questionnaire #$surveyId can be found";
            throw new RecordNotFoundException($msg);
        }

        $responsesTable = TableRegistry::get('Responses');
        $responses = $responsesTable->getCurrentApproved($surveyId);
        if (!$responses) {
            return;
        }

        // Collect alignments for individual responses
        $alignments = [
            'vsLocal' => [],
            'vsParent' => []
        ];
        $areasTable = TableRegistry::get('Areas');
        $actualRanksLocal = $areasTable->getPwrrrRanks($community->local_area_id);
        $actualRanksParent = $areasTable->getPwrrrRanks($community->parent_area_id);
        foreach ($responses as $response) {
            $responseRanks = [
                'production' => $response->production_rank,
                'wholesale' => $response->wholesale_rank,
                'retail' => $response->retail_rank,
                'residential' => $response->residential_rank,
                'recreation' => $response->recreation_rank
            ];

            // Calculate each response's PWRRR alignment
            $alignmentVsLocal = $responsesTable->calculateAlignment($actualRanksLocal, $responseRanks);
            $alignments['vsLocal'][$response->id] = $alignmentVsLocal;
            $alignmentVsParent = $responsesTable->calculateAlignment($actualRanksParent, $responseRanks);
            $alignments['vsParent'][$response->id] = $alignmentVsParent;

            // Update stored response alignment if it differs from the value that was just calculated
            if ($alignmentVsLocal != $response->alignment_vs_local) {
                $response = $responsesTable->patchEntity($response, [
                    'alignment_vs_local' => $alignmentVsLocal
                ]);
            }
            if ($alignmentVsParent != $response->alignment_vs_parent) {
                $response = $responsesTable->patchEntity($response, [
                    'alignment_vs_parent' => $alignmentVsParent
                ]);
            }
            if ($response->dirty()) {
                $responsesTable->save($response);
            }
        }

        // Average the alignments of all responses to get total alignments
        $alignmentVsLocal = array_sum($alignments['vsLocal']) / count($alignments['vsLocal']);
        $alignmentVsParent = array_sum($alignments['vsParent']) / count($alignments['vsParent']);

        // Save alignments
        $survey = $this->patchEntity($survey, [
            'alignment_vs_local' => (int)$alignmentVsLocal,
            'alignment_vs_parent' => (int)$alignmentVsParent,
            'alignment_calculated_date' => Time::now()
        ]);
        if ($survey->errors()) {
            $msg = 'There was an error updating that questionnaire\'s response alignments: ';
            $msg .= print_r($survey->errors(), true);
            throw new InternalErrorException($msg);
        }
        $this->save($survey);
    }
}
