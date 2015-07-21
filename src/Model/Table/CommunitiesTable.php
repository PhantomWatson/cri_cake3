<?php
namespace App\Model\Table;

use App\Model\Entity\Community;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Communities Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Areas
 * @property \Cake\ORM\Association\HasMany $Purchases
 * @property \Cake\ORM\Association\HasMany $Surveys
 * @property \Cake\ORM\Association\HasMany $SurveysBackup
 */
class CommunitiesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('communities');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Areas', [
            'foreignKey' => 'area_id'
        ]);
        $this->hasMany('Purchases', [
            'foreignKey' => 'community_id'
        ]);
        $this->hasMany('Surveys', [
            'foreignKey' => 'community_id'
        ]);
        $this->hasMany('SurveysBackup', [
            'foreignKey' => 'community_id'
        ]);
        $this->hasOne('OfficialSurveys', [
            'className' => 'Surveys',
            'foreignKey' => 'community_id',
            'conditions' => ['OfficialSurveys.type' => 'official'],
            'dependent' => true
        ]);
        $this->hasOne('OrganizationSurveys', [
            'className' => 'Surveys',
            'foreignKey' => 'community_id',
            'conditions' => ['OrganizationSurveys.type' => 'organization'],
            'dependent' => true
        ]);
        $this->belongsToMany('Consultant', [
            'className' => 'User',
            'joinTable' => 'communities_consultants',
            'foreignKey' => 'community_id',
            'targetForeignKey' => 'consultant_id',
            'saveStrategy' => 'replace'
        ]);
        $this->belongsToMany('Client', [
            'className' => 'User',
            'joinTable' => 'clients_communities',
            'foreignKey' => 'community_id',
            'associationForeignKey' => 'client_id',
            'saveStrategy' => 'replace'
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
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->add('public', 'valid', ['rule' => 'boolean'])
            ->requirePresence('public', 'create')
            ->notEmpty('public');

        $validator
            ->add('fast_track', 'valid', ['rule' => 'boolean'])
            ->requirePresence('fast_track', 'create')
            ->notEmpty('fast_track');

        $validator
            ->add('score', 'valid', ['rule' => 'decimal'])
            ->requirePresence('score', 'create')
            ->notEmpty('score');

        $validator
            ->add('town_meeting_date', 'valid', ['rule' => 'date'])
            ->allowEmpty('town_meeting_date');

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
        $rules->add($rules->existsIn(['area_id'], 'Areas'));
        return $rules;
    }

    /**
     * @param int $communityId
     * @return int
     */
    public function getAreaId($communityId)
    {
        $community = $this->get($communityId);
        return $community->area_id;
    }

    /**
     * @param int $communityId
     * @return GoogleCharts
     */
    public function getPwrBarChart($communityId)
    {
        $areaId = $this->getAreaId($communityId);
        return $this->Areas->getPwrBarChart($areaId);
    }

    /**
     * @param int $communityId
     * @return array
     */
    public function getPwrTable($communityId)
    {
        $areaId = $this->getAreaId($communityId);
        return $this->Areas->getPwrTable($areaId);
    }

    /**
     * @param int $communityId
     * @return GoogleCharts
     */
    public function getEmploymentLineChart($communityId)
    {
        $areaId = $this->getAreaId($communityId);
        return $this->Areas->getEmploymentLineChart($areaId);
    }

    /**
     * @param int $communityId
     * @return array
     */
    public function getEmploymentGrowthTableData($communityId)
    {
        $areaId = $this->getAreaId($communityId);
        return $this->Areas->getEmploymentGrowthTableData($areaId);
    }

    /**
     * @param int $communityId
     * @return int
     */
    public function getConsultantCount($communityId)
    {
        $result = $this->find('all')
            ->select(['Communities.id'])
            ->where(['Communities.id' => $communityId])
            ->contain([
                'Consultant' => function ($q) {
                    return $q->select(['Consultant.id']);
                }
            ])
            ->first();
        if ($result) {
            return count($result['consultants']);
        }
        return 0;
    }

    /**
     * Returns a an array of communities that a consultant is assigned to
     * @param int $consultant_id
     * @return array $communityId => $community_name
     */
    public function getConsultantCommunityList($consultantId)
    {
        $consultant = $this->Consultants->get($consultantId);
        if ($consultant->all_communities) {
            return $this->find('list')
                ->order(['Communities.name' => 'ASC']);
        }
        $result = $this->Consultants->find('all')
            ->where(['Consultants.id' => $consultantId])
            ->contain([
                'ConsultantCommunities' => function ($q) {
                    return $q
                        ->select(['id', 'name'])
                        ->order(['ConsultantCommunities.name' => 'ASC']);
                }
            ])
            ->first();
        $retval = [];
        foreach ($result['consultant_communities'] as $community) {
            $id = $community['id'];
            $name = $community['name'];
            $retval[$id] = $name;
        }
        return $retval;
    }

    /**
     * Returns the consultants assigned to this community
     * @param int $communityId
     * @return array
     */
    public function getConsultants($communityId)
    {
        $result = $this->find('all')
            ->select(['id'])
            ->where(['id' => $communityId])
            ->contain([
                'Consultants' => function ($q) {
                    return $q->select([
                        'Consultants.id',
                        'Consultants.name',
                        'Consultants.email'
                    ]);
                }
            ])
            ->first();
        return isset($result['consultants']) ? $result['consultants'] : [];
    }

    /**
     * Returns a an array of communities that a client is assigned to
     * @param int $clientId
     * @return array $communityId => $community_name
     */
    public function getClientCommunityList($clientId = null)
    {
        $Users = TableRegistry::get('Users');
        $query = $Users->find('all')
            ->select(['id'])
            ->contain([
                'ClientCommunities' => function ($q) {
                    return $q
                        ->select([
                            'ClientCommunities.id',
                            'ClientCommunities.name'
                        ])
                        ->order(['ClientCommunities.name' => 'ASC']);
                }
            ]);
        $conditions = ['User.role' => 'client'];
        if ($clientId) {
            $conditions['User.id'] = $clientId;
        }
        $query->where($conditions);
        if ($clientId) {
            $query->first();
        }
        $results = $query->toArray();

        if ($clientId) {
            return Hash::combine($results['client_communities'], '{n}.id', '{n}.name');
        }
        return Hash::combine($results, '{n}.client_communities.{n}.id', '{n}.client_communities.{n}.name');
    }

    /**
     * Returns the ID of the (first) Community associated with the specified client, or NULL if no such community is found.
     * @param int $clientId
     * @return int|null
     */
    public function getClientCommunityId($clientId)
    {
        $communities = $this->getClientCommunityList($clientId);
        if (empty($communities)) {
            return null;
        }
        $communityIds = array_keys($communities);
        return $communityIds[0];
    }

    /**
     * Returns an arbitrary client ID associated with the selected community
     * @param int $communityId
     * @return int
     */
    public function getCommunityClientId($communityId)
    {
        $result = $this->find('all')
            ->select(['client_id'])
            ->join([
                'alias' => 'Client',
                'conditions' => ['Client.community_id' => 'Community.id'],
                'table' => 'clients_communities',
                'type' => 'LEFT',

            ])
            ->first();
        return $result ? $result['client']['client_id'] : null;
    }

    /**
     * @param int $communityId
     * @return array
     */
    public function getClients($communityId)
    {
        $result = $this->find('all')
            ->select(['id'])
            ->where(['id' => $communityId])
            ->contain([
                'Client' => function ($q) {
                    return $q
                        ->select(['id', 'name', 'email'])
                        ->order(['Client.name' => 'ASC']);
                }
            ])
            ->first();

        $retval = [];
        if (isset($result['clients'])) {
            foreach ($result['clients'] as $client) {
                unset($client['clients_communities']);
                $retval[] = $client;
            }
        }
        return $retval;
    }

    /**
     * @param int $communityId
     * @return int
     */
    public function getClientCount($communityId)
    {
        $clients = $this->getClients($communityId);
        return count($clients);
    }

    /**
     * @param int $communityId
     * @param boolean $isAdmin
     * @return array
     */
    public function getProgress($communityId, $isAdmin = false)
    {
        $Products = TableRegistry::get('Product');
        $Surveys = TableRegistry::get('Survey');
        $Respondents = TableRegistry::get('Respondent');
        $criteria = [];


        // Step 1
        $criteria[1]['client_assigned'] = [
            'At least one client account has been created for this community',
            $this->getClientCount($communityId) > 0
        ];

        $criteria[1]['survey_purchased'] = [
            'Purchased Community Leadership Alignment Assessment ($3,500)',
            $Products->isPurchased($communityId, 1)
        ];


        // If survey is not ready, put this at the end of step one
        // Otherwise, at the beginning of step two
        $surveyId = $Surveys->getSurveyId($communityId, 'official');
        $survey = $Surveys->get($surveyId);
        $surveyCreated = $Surveys->hasBeenCreated($communityId, 'official');
        if ($surveyCreated) {
            $note = '<br />Survey URL: <a href="'.$survey->sm_url.'">'.$survey->sm_url.'</a>';
        } else {
            $note =  '';
        }
        $step = $surveyCreated ? 2 : 1;
        $criteria[$step]['survey_created'] = [
            'Leadership alignment assessment survey has been prepared'.$note,
            $surveyCreated
        ];


        // Step 2
        if ($surveyId) {
            $count = $Respondents->getInvitedCount($surveyId);
            $note = $count ? " ($count ".__n('invitation', 'invitations', $count).' sent)' : '';
        } else {
            $count = 0;
            $note = '';
        }
        $criteria[2]['invitations_sent'] = [
            'Community leaders have been sent survey invitations'.$note,
            $surveyId && $count > 0
        ];

        if ($surveyId) {
            $Responses = TableRegistry::get('Response');
            $count = $Responses->getDistinctCount($surveyId);
            $note = $count ? " ($count ".__n('response', 'responses', $count).' received)' : '';
        } else {
            $count = 0;
            $note = '';
        }
        $criteria[2]['responses_received'] = [
            'Responses to the survey have been collected'.$note,
            $surveyId && $count > 0
        ];

        $criteria[2]['response_threshhold_reached'] = [
            'At least 50% of invited community leaders have responded to the survey',
            $Surveys->getInvitedResponsePercentage($surveyId) >= 50
        ];

        if ($Surveys->hasUninvitedResponses($surveyId)) {
            $criteria[2]['unapproved_addressed'] = [
                'All unapproved responses have been approved or dismissed',
                ! $Surveys->hasUnaddressedUnapprovedRespondents($surveyId)
            ];
        }

        $criteria[2]['alignment_calculated'] = [
            'Community leadership alignment calculated',
            $survey->alignment_passed != 0
        ];

        if ($survey->alignment_passed == 0) {
            $note = ' (alignment not yet calculated)';
        } elseif ($isAdmin) {
            $alignment = $survey->alignment;
            $note = " ($alignment% aligned)";
        } else {
            $note = '';
        }
        $purchasedLeadershipSummit = $Products->isPurchased($communityId, 2);
        if (! $purchasedLeadershipSummit) {
            $criteria[2]['alignment_passed'] = [
                'Passed leadership alignment assessment'.$note,
                 $survey->alignment_passed == 1
            ];
        }

        if ($survey->alignment_passed == -1 || $purchasedLeadershipSummit) {
            $criteria[2]['consultant_assigned'] = [
                'At least one consultant has been assigned to this community',
                $this->getConsultantCount($communityId) > 0
            ];

            $criteria[2]['summit_purchased'] = [
                'Purchased Leadership Summit ($1,500)',
                $purchasedLeadershipSummit
            ];
        }

        if (! $purchasedLeadershipSummit && ($survey->alignment_passed == 0 || $survey->alignment_passed == 1)) {
            $criteria[2]['survey_purchased'] = [
                'Purchased Community Organizations Alignment Assessment ($3,500)',
                $Products->isPurchased($communityId, 3)
            ];
        }


        // Step 2.5
        if ($purchasedLeadershipSummit) {
            $criteria['2.5']['alignment_passed'] = [
                'Passed leadership alignment assessment'.$note,
                 $survey->alignment_passed == 1
            ];
        }

        if ($survey->alignment_passed == -1 || $purchasedLeadershipSummit) {
            $criteria['2.5']['survey_purchased'] = [
                'Purchased Community Organizations Alignment Assessment ($3,500)',
                $Products->isPurchased($communityId, 3)
            ];
        }


        // Step 3
        $surveyId = $Surveys->getSurveyId($communityId, 'organization');
        $survey->get($surveyId);
        $surveyCreated = $Surveys->hasBeenCreated($communityId, 'organization');
        if ($surveyCreated) {
            $note = '<br />Survey URL: <a href="'.$survey->sm_url.'">'.$survey->sm_url.'</a>';
        } else {
            $note =  '';
        }
        $criteria[3]['survey_created'] = [
            'Community organization alignment assessment survey has been prepared'.$note,
            $surveyCreated
        ];

        if ($surveyId) {
            $count = $Respondents->getCount($surveyId);
            $note = $count ? " ($count ".__n('response', 'responses', $count).' received)' : '';
        }
        $criteria[3]['responses_received'] = [
            'Responses to the survey have been collected'.$note,
            $surveyId && $count > 0
        ];

        $criteria[3]['alignment_calculated'] = [
            'Community organization alignment calculated',
            $survey->alignment_passed != 0
        ];

        if ($survey->alignment_passed == 0) {
            $note = ' (alignment not yet calculated)';
        } elseif ($isAdmin) {
            $note = " ({$survey->alignment}% aligned)";
        } else {
            $note = '';
        }
        $purchasedCommunitySummit = $Products->isPurchased($communityId, 4);
        if (! $purchasedCommunitySummit) {
            $criteria[3]['alignment_passed'] = [
                'Passed community alignment assessment'.$note,
                 $survey->alignment_passed == 1
            ];
        }

        if ($survey->alignment_passed == -1 || $purchasedCommunitySummit) {
            $criteria[3]['consultant_assigned'] = [
                'At least one consultant has been assigned to this community',
                $this->getConsultantCount($communityId) > 0
            ];

            $criteria[3]['summit_purchased'] = [
                'Purchased Facilitated Community Awareness Conversation ($1,500)',
                $purchasedCommunitySummit
            ];
        }

        if (! $purchasedCommunitySummit) {
            $criteria[3]['policy_dev_purchased'] = [
                'Purchased PwR3 Policy Development ($5,000)',
                $Products->isPurchased($communityId, 5)
            ];
        }


        // Step 3.5
        if ($purchasedCommunitySummit) {
            $criteria['3.5']['alignment_passed'] = [
                'Passed community alignment assessment'.$note,
                 $survey->alignment_passed == 1
            ];

            $criteria['3.5']['policy_dev_purchased'] = [
                'Purchased PwR3 Policy Development ($5,000)',
                $Products->isPurchased($communityId, 5)
            ];
        }


        // Step 4
        $community = $this->get($communityId);
        $note = $community->town_meeting_date ? ' ('.date('F jS, Y', strtotime($community->town_meeting_date)).')': '';
        $criteria[4]['meeting_scheduled'] = [
            'Scheduled town meeting'.$note,
            $community->town_meeting_date != null
        ];
        if ($meetingDate != null) {
            $criteria[4]['meeting_held'] = [
                'Held town meeting',
                $community->town_meeting_date <= date('Y-m-d')
            ];
        }

        return $criteria;
    }
}
