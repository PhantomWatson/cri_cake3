<?php
namespace App\Model\Table;

use App\Model\Entity\Respondent;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Respondents Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Surveys
 * @property \Cake\ORM\Association\BelongsTo $SmRespondents
 * @property \Cake\ORM\Association\HasMany $Responses
 */
class RespondentsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('respondents');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Surveys', [
            'foreignKey' => 'survey_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('Responses', [
            'foreignKey' => 'respondent_id'
        ]);
        $this->addBehavior('SurveyMonkey');
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
            ->add('email', 'valid', ['rule' => 'email'])
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->add('invited', 'valid', ['rule' => 'boolean'])
            ->requirePresence('invited', 'create')
            ->notEmpty('invited');

        $validator
            ->add('approved', 'valid', ['rule' => 'numeric'])
            ->requirePresence('approved', 'create')
            ->notEmpty('approved');

        $validator
            ->add('response_date', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('response_date');

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
        $rules->add($rules->existsIn(['survey_id'], 'Surveys'));
        return $rules;
    }

    public function getList($surveyId, $invited = null)
    {
        $conditions = ['survey_id' => $surveyId];
        if (isset($invited)) {
            $conditions['invited'] = $invited;
        }
        return $this->find('list')
            ->where($conditions)
            ->toArray();
    }

    public function getInvitedList($surveyId)
    {
        return $this->getList($surveyId, true);
    }

    public function getInvited($surveyId)
    {
        return $this->find('all')
            ->select(['id', 'name', 'email'])
            ->where([
                'survey_id' => $surveyId,
                'invited' => 1
            ])
            ->all();
    }

    public function getUninvitedList($surveyId)
    {
        return $this->getList($surveyId, false);
    }

    /**
     * Collects any new SurveyMonkey respondents and returns an array
     * @param int $surveyId Key for record in 'surveys' table, not the SurveyMonkey survey_id
     * @return array [success, [respondent_id => date_modified] || error]
     */
    public function getNewFromSurveyMonkey($surveyId)
    {
        $surveysTable = TableRegistry::get('Surveys');
        try {
            $survey = $surveysTable->get($surveyId);
        } catch (RecordNotFoundException $e) {
            return [false, 'Survey #'.$surveyId.' not found'];
        }

        if (! $survey->sm_id) {
            return [false, 'Survey #'.$surveyId.' has not yet been linked to SurveyMonkey', null];
        }

        $recordedRespondents = $this->getAllForSurvey($surveyId);
        $SurveyMonkey = $this->getSurveyMonkeyObject();
        $page = 1;
        $pageSize = 1000;
        $lastDateChecked = $survey->respondents_last_modified_date ? $survey->respondents_last_modified_date->format('Y-m-d H:i:s') : null;
        $retval = [];
        $lastModifiedDates = [];
        $surveyMonkeySurveyId = (string) $survey->sm_id;
        while (true) {
            $params = [
                'order_asc' => true,
                'order_by' => 'date_modified',
                'fields' => [
                    'email',
                    'status',
                    'date_modified'
                ],
                'page' => $page,
                'page_size' => $pageSize,
                'survey_id' => $surveyMonkeySurveyId
            ];
            if ($lastDateChecked) {
                $params['start_modified_date'] = $lastDateChecked;
            }

            $result = $SurveyMonkey->getRespondentList($surveyMonkeySurveyId, $params);
            if (! $result['success']) {
                return [false, $result['message'], null];
            }

            $respondents = $result['data']['respondents'];
            if (empty($respondents) && $page == 1) {
                return [true, [], null];
            }

            foreach ($respondents as $respondent) {
                if ($respondent['status'] == 'completed') {
                    $respondentSmId = $respondent['respondent_id'];
                    $retval[$respondentSmId] = $respondent['date_modified'];
                }

                if (! $lastDateChecked || $lastDateChecked < $respondent['date_modified']) {
                    $lastDateChecked = $respondent['date_modified'];
                }
            }

            // If there may be more respondents on additional pages of results, continue
            if (count($respondents) == $pageSize) {
                $page++;

                // The SurveyMonkey API limits us to 2 API requests per second.
                // For extra safety, we'll delay for one second before the second API call.
                sleep(1);
            } else {
                break;
            }
        }

        return [true, $retval];
    }

    public function getAllForSurvey($surveyId)
    {
        return $this->find('all')
            ->select(['id', 'email', 'sm_respondent_id'])
            ->where(['survey_id' => $surveyId])
            ->toArray();
    }

    public function getInvitedCount($surveyId)
    {
        return $this->find('all')
            ->where([
                'survey_id' => $surveyId,
                'invited' => true
            ])
            ->count();
    }

    public function getCount($surveyId)
    {
        return $this->find('all')
            ->where(['survey_id' => $surveyId])
            ->count();
    }

    public function getUninvitedCount($surveyId)
    {
        return $this->find('all')
            ->where([
                'survey_id' => $surveyId,
                'invited' => false
            ])
            ->count();
    }

    public function getUnaddressedUnapproved($surveyId)
    {
        return $this->find('all')
            ->select(['id', 'email', 'name', 'approved'])
            ->where([
                'survey_id' => $surveyId,
                'approved' => 0
            ])
            ->order(['created' => 'DESC'])
            ->toArray();
    }

    /**
     * Returns list of unapproved and not-dismissed respondents with non-blank email addresses
     * @param int $surveyId
     * @return array
     */
    public function getUnaddressedUnapprovedList($surveyId)
    {
        return $this->find('list', [
                'keyField' => 'id',
                'valueField' => 'email'
            ])
            ->where([
                'survey_id' => $surveyId,
                'approved' => 0
            ])
            ->where(function ($exp, $q) {
                return $exp->notEq('email', '');
            })
            ->order(['created' => 'DESC'])
            ->toArray();
    }

    /**
     * Returns list of approved respondents with non-blank email addresses
     * @param int $surveyId
     * @return array
     */
    public function getApprovedList($surveyId)
    {
        return $this->find('list', [
                'keyField' => 'id',
                'valueField' => 'email'
            ])
            ->where([
                'survey_id' => $surveyId,
                'approved' => 1
            ])
            ->where(function ($exp, $q) {
                return $exp->notEq('email', '');
            })
            ->order(['email' => 'ASC'])
            ->toArray();
    }

    public function getDismissed($surveyId)
    {
        return $this->find('all')
            ->select(['id', 'email', 'name', 'approved'])
            ->where([
                'survey_id' => $surveyId,
                'approved' => -1
            ])
            ->order(['created' => 'DESC'])
            ->toArray();
    }

    /**
     * Returns TRUE if the client is authorized to approved a given respondent
     * @param int $clientId
     * @param int $respondentId
     * @return boolean
     */
    public function clientCanApproveRespondent($clientId, $respondentId)
    {
        $respondent = $this->get($respondentId);
        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($respondent->survey_id);
        $communitiesTable = TableRegistry::get('Communities');
        $assignedCommunityId = $communitiesTable->getClientCommunityId($clientId);
        $idsFound = (boolean) ($respondent->survey_id && $survey->community_id);
        $communityIsAssigned = $survey->community_id == $assignedCommunityId;
        return $idsFound && $communityIsAssigned;
    }
}
