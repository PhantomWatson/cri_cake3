<?php
namespace App\Model\Table;

use App\Model\Entity\Respondent;
use Cake\Network\Exception\InternalErrorException;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
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
            ->requirePresence('email', 'create');

        $validator
            ->requirePresence('name', 'create');

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

    /**
     * Returns a list of respondents for the selected survey
     *
     * @param int $surveyId Survey ID
     * @param null|bool $invited The required value for Respondents.invited (optional)
     * @return array
     */
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

    /**
     * Returns a list of invited respondents for the specified survey
     *
     * @param int $surveyId Survey ID
     * @return array
     */
    public function getInvitedList($surveyId)
    {
        return $this->getList($surveyId, true);
    }

    /**
     * Returns a result set of all invited respondents for the specified survey
     *
     * @param int $surveyId Survey ID
     * @return \Cake\Datasource\ResultSetInterface
     */
    public function getInvited($surveyId)
    {
        return $this->find('all')
            ->select(['id', 'name', 'email', 'title'])
            ->where([
                'survey_id' => $surveyId,
                'invited' => 1
            ])
            ->order(['name' => 'ASC'])
            ->all();
    }

    /**
     * Returns a list of all uninvited respondents to the specified survey
     *
     * @param int $surveyId Survey ID
     * @return array
     */
    public function getUninvitedList($surveyId)
    {
        return $this->getList($surveyId, false);
    }

    /**
     * Collects any new SurveyMonkey respondents and returns an array
     *
     * @param int $surveyId Survey ID
     * @return array [success, [respondent_id => date_modified] || error]
     */
    public function getNewFromSurveyMonkey($surveyId)
    {
        $surveysTable = TableRegistry::get('Surveys');
        try {
            $survey = $surveysTable->get($surveyId);
        } catch (RecordNotFoundException $e) {
            return [false, "Questionnaire #$surveyId not found"];
        }

        if (!$survey->sm_id) {
            return [false, "Questionnaire #$surveyId has not yet been linked to SurveyMonkey", null];
        }

        $SurveyMonkey = $this->getSurveyMonkeyObject();
        $page = 1;
        $pageSize = 1000;
        if ($survey->respondents_last_modified_date) {
            $lastResponseDate = $survey->respondents_last_modified_date->format('Y-m-d H:i:s');
        } else {
            $lastResponseDate = null;
        }
        $retval = [];
        $surveyMonkeySurveyId = (string)$survey->sm_id;
        while (true) {
            $params = [
                'sort_order' => 'ASC',
                'sort_by' => 'date_modified',
                'page' => $page,
                'per_page' => $pageSize,
                'status' => 'completed'
            ];
            if ($lastResponseDate) {
                $params['start_modified_at'] = $lastResponseDate;
            }

            $result = $SurveyMonkey->getRespondentList($surveyMonkeySurveyId, $params);
            if (! $result['success']) {
                return [false, $result['message'], null];
            }

            $respondents = $result['data']['data'];
            if (empty($respondents) && $page == 1) {
                return [true, [], null];
            }

            foreach ($respondents as $respondent) {
                $respondentSmId = $respondent['id'];
                $retval[$respondentSmId] = $respondent['date_modified'];

                if (! $lastResponseDate || $lastResponseDate < $respondent['date_modified']) {
                    $lastResponseDate = $respondent['date_modified'];
                }
            }

            // If there may be more respondents on additional pages of results, continue
            if (count($respondents) == $pageSize) {
                $page++;
            } else {
                break;
            }
        }

        return [true, $retval];
    }

    /**
     * Returns an array with all respondents to the specified survey
     *
     * @param int $surveyId Survey ID
     * @return array
     */
    public function getAllForSurvey($surveyId)
    {
        return $this->find('all')
            ->select(['id', 'email', 'sm_respondent_id'])
            ->where(['survey_id' => $surveyId])
            ->toArray();
    }

    /**
     * Returns the number of respondents invited to complete a survey
     *
     * @param int $surveyId Survey ID
     * @return int
     */
    public function getInvitedCount($surveyId)
    {
        return $this->find('all')
            ->where([
                'survey_id' => $surveyId,
                'invited' => true
            ])
            ->count();
    }

    /**
     * Returns the number of respondents associated with a survey
     *
     * @param int $surveyId Survey ID
     * @return int
     */
    public function getCount($surveyId)
    {
        return $this->find('all')
            ->where(['survey_id' => $surveyId])
            ->count();
    }

    /**
     * Returns the number of respondents associated with a survey who have not been invited
     *
     * @param int $surveyId Survey ID
     * @return int
     */
    public function getUninvitedCount($surveyId)
    {
        return $this->find('all')
            ->where([
                'survey_id' => $surveyId,
                'invited' => false
            ])
            ->count();
    }

    /**
     * Returns an array of respondents for a survey who have not been approved
     *
     * @param int $surveyId Survey ID
     * @return array
     */
    public function getUnaddressedUnapproved($surveyId)
    {
        return $this->find('all')
            ->select(['id', 'email', 'name', 'title', 'approved'])
            ->where([
                'survey_id' => $surveyId,
                'approved' => 0
            ])
            ->order(['created' => 'DESC'])
            ->toArray();
    }

    /**
     * Returns list of unapproved and not-dismissed respondents with non-blank email addresses
     *
     * @param int $surveyId Survey ID
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
     *
     * @param int $surveyId Survey ID
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

    /**
     * Returns an array of all dismissed respondents
     *
     * @param int $surveyId Survey ID
     * @return array
     */
    public function getDismissed($surveyId)
    {
        return $this->find('all')
            ->select(['id', 'email', 'name', 'title', 'approved'])
            ->where([
                'survey_id' => $surveyId,
                'approved' => -1
            ])
            ->order(['created' => 'DESC'])
            ->toArray();
    }

    /**
     * Returns TRUE if the client is authorized to approved a given respondent
     *
     * @param int $clientId Client user ID
     * @param int $respondentId Respondent ID
     * @return bool
     */
    public function clientCanApproveRespondent($clientId, $respondentId)
    {
        $respondent = $this->get($respondentId);
        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($respondent->survey_id);
        $communitiesTable = TableRegistry::get('Communities');
        $assignedCommunityId = $communitiesTable->getClientCommunityId($clientId);
        $idsFound = (bool)($respondent->survey_id && $survey->community_id);
        $communityIsAssigned = $survey->community_id == $assignedCommunityId;

        return $idsFound && $communityIsAssigned;
    }

    /**
     * Returns a respondent record for the specified survey and
     * matching either the same smRespondentId or email address.
     *
     * @param int $surveyId Survey ID
     * @param array $respondent Respondent array
     * @param int $smRespondentId SurveyMonkey respondent ID
     * @return Respondent
     */
    public function getMatching($surveyId, $respondent, $smRespondentId)
    {
        return $this->find('all')
            ->select(['id', 'sm_respondent_id', 'name'])
            ->where([
                // Same survey and either the same smRespondentId OR (actual) email address
                'survey_id' => $surveyId,
                'OR' => [
                    function ($exp, $q) use ($respondent) {
                        // @ and . required, weeds out "email not listed" values
                        return $exp
                            ->like('email', '%@%.%')
                            ->eq('email', $respondent['email']);
                    },
                    ['sm_respondent_id' => $smRespondentId]
                ]
            ])
            ->first();
    }

    /**
     * Returns TRUE if this respondent's responses should be auto-approved
     *
     * @param Survey $survey Survey entity
     * @param string $email Email address
     * @return bool
     */
    public function isAutoApproved($survey, $email)
    {
        // All organization survey responses are auto-approved
        if ($survey->type == 'organization') {
            return true;
        }

        // Responses from a community's client are auto-approved
        $usersTable = TableRegistry::get('Users');
        $userId = $usersTable->getIdWithEmail($email);
        if ($userId) {
            return $usersTable->isCommunityClient($survey->community_id, $userId);
        }

        return false;
    }

    /**
     * Returns invited participants with no corresponding responses
     *
     * @param int $surveyId Survey ID
     * @return array
     */
    public function getUnresponsive($surveyId)
    {
        // Get IDs of participants who have responded
        $responsesTable = TableRegistry::get('Responses');
        $responses = $responsesTable->find('all')
            ->select(['respondent_id'])
            ->where(['survey_id' => $surveyId])
            ->toArray();
        $responsiveRespondentIds = Hash::extract($responses, '{n}.respondent_id');
        $responsiveRespondentIds = array_unique($responsiveRespondentIds);

        // Return invitees who haven't
        $query = $this->find('all')
            ->select(['id', 'name', 'title', 'email'])
            ->where([
                'survey_id' => $surveyId
            ])
            ->order(['name' => 'ASC']);
        if (! empty($responsiveRespondentIds)) {
            $query->where([function ($exp, $q) use ($responsiveRespondentIds) {
                return $exp->notIn('id', $responsiveRespondentIds);
            }]);
        }

        return $query->toArray();
    }

    /**
     * Uses the SurveyMonkey API to determine the SurveyMonkey respondent
     * id (aka response ID) corresponding to a CRI respondent ID
     *
     * @param int $respondentId Respondent ID
     * @return string
     * @throws InternalErrorException
     */
    public function getSmRespondentId($respondentId)
    {
        $respondent = $this->find('all')
            ->select(['email', 'survey_id'])
            ->where(['id' => $respondentId])
            ->first();
        $email = $respondent->email;
        $surveyId = $respondent->survey_id;

        $responsesTable = TableRegistry::get('Responses');
        $response = $responsesTable->find('all')
            ->select(['response_date'])
            ->where(['respondent_id' => $respondentId])
            ->order(['response_date' => 'DESC'])
            ->first();
        $responseDate = $response->response_date->i18nFormat('yyyy-MM-dd HH:mm:ss');

        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->find('all')
            ->select(['sm_id'])
            ->where(['id' => $surveyId])
            ->first();
        $smSurveyId = $survey->sm_id;

        $SurveyMonkey = $this->getSurveyMonkeyObject();
        $result = $SurveyMonkey->getRespondentList((string)$smSurveyId, [
            'start_modified_at' => $responseDate
        ]);
        if (! $result['success']) {
            $msg = 'Error retrieving response data from SurveyMonkey.';
            $msg .= ' Details: ' . print_r($result['message'], true);
            throw new InternalErrorException($msg);
        }

        foreach ($result['data']['data'] as $returnedRespondent) {
            $respondent = $responsesTable->extractRespondentInfo($returnedRespondent);
            if ($respondent['email'] == $email) {
                return $returnedRespondent['id'];
            }
        }
        throw new NotFoundException('SurveyMonkey didn\'t return any data about this respondent');
    }
}
