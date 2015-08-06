<?php
namespace App\Model\Table;

use App\Model\Entity\Survey;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Mailer\Email;

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
            ->allowEmpty('sm_url');

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
            ->allowEmpty('respondents_last_modified_date');

        $validator
            ->add('responses_checked', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('responses_checked');

        $validator
            ->add('alignment', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('alignment');

        $validator
            ->add('alignment_passed', 'valid', ['rule' => 'numeric'])
            ->requirePresence('alignment_passed', 'create')
            ->notEmpty('alignment_passed');

        $validator
            ->add('alignment_calculated', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('alignment_calculated');

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
        $rules->add($rules->existsIn(['sm_id'], 'Sms'));
        return $rules;
    }

    /**
     * Returns an array of surveys (arrays with keys 'id' and 'title') currently hosted by SurveyMonkey
     * @return array
     */
    public function getSMSurveyList($params)
    {
        $SurveyMonkey = $this->getSurveyMonkeyObject();
        $page_size = 1000;
        $page = 1;
        $retval = [];
        while (true) {
            $default_params = [
                'fields' => ['title'],
                'page' => $page,
                'page_size' => $page_size
            ];
            $params = array_merge($default_params, $params);
            $result = $SurveyMonkey->getSurveyList($params);
            if (isset($result['data']['surveys']) && ! empty($result['data']['surveys'])) {
                foreach ($result['data']['surveys'] as $survey) {
                    $retval[] = [
                        'sm_id' => $survey['survey_id'],
                        'title' => $survey['title'],
                        'url' => $this->getCachedSMSurveyUrl($survey['survey_id'])
                    ];
                }
                if (count($result['data']['surveys']) == $page_size) {
                    $page++;
                } else {
                    break;
                }
            } else {
                break;
            }
        }
        return $retval;
    }

    public function getSMSurveyUrl($smId = null)
    {
        // Validate ID
        if (! $smId) {
            throw new NotFoundException('SurveyMonkey ID not specified');
        } elseif (! is_numeric($smId)) {
            throw new NotFoundException('SurveyMonkey ID "'.$smId.'" is not numeric');
        }

        // Pull from cache if possible
        $cached = $this->getCachedSMSurveyUrl($smId);
        if ($cached) {
            return $cached;
        }

        // Nab URL from SurveyMonkey
        $SurveyMonkey = $this->getSurveyMonkeyObject();
        $params = [
            'fields' => ['type', 'url']
        ];
        $collectors = $SurveyMonkey->getCollectorList($smId, $params);
        $retval = false;
        if (isset($collectors['data']['collectors']) && ! empty($collectors['data']['collectors'])) {
            foreach ($collectors['data']['collectors'] as $collector) {
                if ($collector['type'] == 'url') {
                    $retval = $collector['url'];
                    break;
                }
            }
        }

        if (empty($retval)) {
            throw new NotFoundException("SurveyMonkey survey #$smId URL not found");
        } else {
            Cache::write($smId, $retval, 'survey_urls');
            return $retval;
        }
    }

    public function getCommunityId($surveyId)
    {
        $survey = $this->get($surveyId);
        return $survey->community_id;
    }

    public function getCachedSMSurveyUrl($smId)
    {
        return Cache::read($smId, 'survey_urls');
    }

    public function sendInvitationEmail($respondentId)
    {
        $respondentsTable = TableRegistry::get('Respondents');
        $respondent = $respondentsTable->get($respondentId);

        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->get($respondent->survey_id);

        $email = new Email('survey_invitation');
        $email->to($respondent->email);
        $email->viewVars([
            'criUrl' => Router::url('/', true),
            'surveyType' => $survey->type,
            'surveyUrl' => $survey->sm_url
        ]);
        return $email->send();
    }

    /**
     * Returns true if the survey exists and has its URL recorded
     * @param int $communityId
     * @param string $surveyType 'official' or 'organization'
     * @return boolean
     */
    public function isOpen($communityId, $surveyType)
    {
        if ($surveyType != 'official' && $surveyType != 'organization') {
            throw new InternalErrorException('Unrecognized survey type: '.$surveyType);
        }

        $communitiesTable = TableRegistry::get('Communities');
        if (! $communitiesTable->exists(['id' => $communityId])) {
            throw new NotFoundException('Could not get survey status. Community (#'.$communityId.') not found.');
        }

        $survey = $this->find('all')
            ->select(['sm_url'])
            ->where([
                'community_id' => $communityId,
                'type' => $surveyType
            ])
            ->first();
        return ! empty($survey->sm_url);
    }

    /**
     * Returns an array of invited_respondent_count, uninvited_respondent_count, and percent_invited_responded for a single survey type or an array of both
     * @param int $communityId
     * @param string $surveyType Optional
     * @return array
     */
    public function getStatus($communityId, $surveyType = null)
    {
        $allTypes = ['official', 'organization'];
        if ($surveyType) {
            if (! in_array($surveyType, $allTypes)) {
                throw new InternalErrorException('Unrecognized survey type: '.$surveyType);
            }
            $types = [$surveyType];
        } else {
            $types = $allTypes;
        }

        $respondentsTable = TableRegistry::get('Respondents');
        $responsesTable = TableRegistry::get('Responses');
        $survey_status = [];

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
            $survey_status[$type] = [
                'invited_respondent_count' => $invitedRespondentCount,
                'uninvited_respondent_count' => $uninvitedRespondentCount,
                'percent_invited_responded' => round($percentInvitedResponded) * 100,
                'responses_checked' => strtotime($survey->responses_checked),
                'survey_id' => $survey->id
            ];
        }
        if ($surveyType) {
            return array_pop($survey_status);
        }
        return $survey_status;
    }

    public function getSurveyId($communityId, $type)
    {
        $survey = $this->find('all')
            ->select(['id'])
            ->where([
                'community_id' => $communityId,
                'type' => $type
            ])
            ->first();
        if (empty($result)) {
            return null;
        }
        return $survey->id;
    }

    public function setChecked($surveyId)
    {
        $survey = $this->get($surveyId);
        $survey->responses_checked = date('Y-m-d H:i:s');
        $this->save($survey);
    }

    public function getChecked($surveyId)
    {
        $survey = $this->get($surveyId);
        return $survey->responses_checked;
    }

    /**
     * Returns the ID of the least-recently-imported survey
     * @return null|int
     */
    public function getIdForAutomatedImport()
    {
        $survey = $this->find('all')
            ->select(['id'])
            ->order(['responses_checked' => 'ASC'])
            ->first();
        if ($survey->isEmpty()) {
            return null;
        }
        return $survey->id;
    }

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
     * Queries SurveyMonkey to determine the IDs associated with the PWRRR-ranking question and its set of answers
     * @param int $smId SurveyMonkey survey ID
     * @return array First value is true/false for success/failure, second value is status message, third is data array for saving Survey with
     */
    public function getQuestionAndAnswerIds($smId)
    {
        $SurveyMonkey = $this->getSurveyMonkeyObject();
        $result = $SurveyMonkey->getSurveyDetails($smId);
        if (! isset($result['data'])) {
            return [false, 'Could not get survey details from SurveyMonkey. This might be a temporary network error.'];
        }

        // Find the appropriate question
        $pwrrrQuestion = null;
        $keyPhrase = 'PWR3 is a tool for thinking about the economic future of your community.';
        foreach ($result['data']['pages'] as $page) {
            foreach ($page['questions'] as $question) {
                if (strpos($question['heading'], $keyPhrase) !== false) {
                    $pwrrrQuestion = $question;
                    break 2;
                }
            }
        }

        if (! $pwrrrQuestion) {
            return [false, 'Error: Could not find a question in this survey containing the phrase "'.$keyPhrase.'"'];
        }

        // Create an array to save this data with
        $sectors = [
            'production',
            'wholesale',
            'recreation',
            'retail',
            'residential',
        ];
        $qnaIdFields = $this->getQnaIdFieldNames();
        $nulls = array_fill(0, count($qnaIdFields), null);
        $data = array_combine($qnaIdFields, $nulls);
        $data['pwrrr_qid'] = $pwrrrQuestion['question_id'];
        foreach ($pwrrrQuestion['answers'] as $answer) {
            // For some reason, in_array($answer['text'], range('1', '5')) doesn't work
            switch ($answer['text']) {
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                    $field = $answer['text'].'_aid';
                    $data[$field] = $answer['answer_id'];
                    continue;
            }
            foreach ($sectors as $sector) {
                if (stripos($answer['text'], $sector) !== false) {
                    $field = $sector.'_aid';
                    $data[$field] = $answer['answer_id'];
                    break;
                }
            }
        }

        // Make sure all fields have values
        foreach ($data as $field => $value) {
            if (! $value) {
                $answer = str_replace('_aid', '', $field);
                return [false, 'Error: Could not find the answer ID for the answer "'.$answer.'".'];
            }
        }

        return [true, '', $data];
    }

    /**
     * Sets the SurveyMonkey Q&A IDs associated with the PWRRR-ranking question and its set of answers
     * @param int $smId SurveyMonkey survey ID
     * @return array [boolean success/failure indicator, result message]
     */
    public function setQuestionAndAnswerIds($smId)
    {
        $survey = $this->find('all')
            ->select(['id'])
            ->where(['sm_id' => $smId])
            ->first();
        if ($survey->isEmpty()) {
            return [false, 'Error: No survey has been recorded with SurveyMonkey id "'.$smId.'".'];
        }
        $data = $this->getQuestionAndAnswerIds($smId)[2];
        $this->patchEntity($survey, $data);
        if ($this->save($survey)) {
            return [true, 'Question and answer IDs saved.'];
        }
        return [false, 'Error: Could not save question and answer IDs'];
    }

    /**
     * Returns the percent (0-100) of invited respondents who have responded
     * @param $surveyId int
     * @return int
     */
    public function getInvitedResponsePercentage($surveyId)
    {
        $respondentsTable = TableRegistry('Respondents');
        $invitations = $respondentsTable->getInvitedCount($surveyId);
        $responsesTable = TableRegistry('Responses');
        $responses = $responsesTable->getInvitedCount($surveyId);
        if (! $invitations || ! $responses) {
            return 0;
        }
        return round(($responses / $invitations) * 100);
    }

    public function hasBeenCreated($communityId, $surveyType)
    {
        $count = $this->find('all')
            ->where([
                'community_id' => $communityId,
                'type' => $surveyType,
                'sm_url NOT' => ''
            ])
            ->count();
        return $count > 0;
    }

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
     * Returns true if responses have been received since alignment was last set by an admin
     * @param int $surveyId
     * @return boolean
     */
    public function newResponsesHaveBeenReceived($surveyId) {
        try {
            $survey = $this->get($surveyId);
        } catch (RecordNotFoundException $e) {
            return false;
        }
        $responsesTable = TableRegistry::get('Responses');
        $count = $responsesTable->find('all')
            ->where([
                'survey_id' => $surveyId,
                'created >' => $survey->alignment_calculated
            ])
            ->count();
        return $count > 0;
    }
}
