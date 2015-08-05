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
            ->first()
            ->toArray();
        return ! empty($survey->sm_url);
    }
}
