<?php
namespace App\Model\Table;

use App\Model\Entity\Respondent;
use Cake\I18n\Time;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Respondents Model
 *
 * @property \App\Model\Table\SurveysTable|\Cake\ORM\Association\BelongsTo $Surveys
 * @property \Cake\ORM\Association\BelongsTo $SmRespondents
 * @property \App\Model\Table\ResponsesTable|\Cake\ORM\Association\HasMany $Responses
 * @method Query findBySurveyIdAndEmail($surveyId, $email)
 * @method \App\Model\Entity\Respondent get($primaryKey, $options = [])
 * @method \App\Model\Entity\Respondent newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Respondent[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Respondent|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Respondent patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Respondent[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Respondent findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
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
        $this->setTable('respondents');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Surveys', [
            'foreignKey' => 'survey_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('Responses', [
            'foreignKey' => 'respondent_id'
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
            ->requirePresence('email', 'create')
            ->add('email', 'validFormat', [
                'rule' => 'email',
                'message' => 'Email address must be in valid format'
            ]);

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
     * Returns the earliest date of an invitation sent for the specified survey
     *
     * @param int $surveyId Survey ID
     * @return Time|null
     */
    public function getFirstInvitationDate($surveyId)
    {
        $result = $this->find('all')
            ->select(['created'])
            ->where([
                'survey_id' => $surveyId,
                'invited' => 1
            ])
            ->order(['created' => 'ASC'])
            ->first();

        return $result ? $result['created'] : null;
    }
}
