<?php
namespace App\Model\Table;

use Cake\Chronos\Date;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Communities Model
 *
 * @property \Cake\ORM\Association\BelongsTo $LocalAreas
 * @property \Cake\ORM\Association\BelongsTo $ParentAreas
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
        $this->belongsTo('LocalAreas', [
            'className' => 'Areas',
            'foreignKey' => 'local_area_id',
        ]);
        $this->belongsTo('ParentAreas', [
            'className' => 'Areas',
            'foreignKey' => 'parent_area_id',
        ]);
        $this->hasMany('Purchases', [
            'foreignKey' => 'community_id'
        ]);
        $this->hasMany('OptOuts', [
            'foreignKey' => 'community_id'
        ]);
        $this->hasMany('Surveys', [
            'foreignKey' => 'community_id'
        ]);
        $this->hasMany('SurveysBackup', [
            'foreignKey' => 'community_id'
        ]);
        $this->hasOne('OfficialSurvey', [
            'className' => 'Surveys',
            'foreignKey' => 'community_id',
            'conditions' => ['OfficialSurvey.type' => 'official'],
            'dependent' => true
        ]);
        $this->hasOne('OrganizationSurvey', [
            'className' => 'Surveys',
            'foreignKey' => 'community_id',
            'conditions' => ['OrganizationSurvey.type' => 'organization'],
            'dependent' => true
        ]);
        $this->belongsToMany('Consultants', [
            'className' => 'Users',
            'joinTable' => 'communities_consultants',
            'foreignKey' => 'community_id',
            'targetForeignKey' => 'consultant_id',
            'saveStrategy' => 'replace'
        ]);
        $this->belongsToMany('Clients', [
            'className' => 'Users',
            'joinTable' => 'clients_communities',
            'foreignKey' => 'community_id',
            'targetForeignKey' => 'client_id',
            'saveStrategy' => 'replace'
        ]);
        $this->hasMany('ActivityRecords', [
            'foreignKey' => 'community_id'
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
            ->requirePresence('parent_area_id', 'create')
            ->notEmpty('parent_area_id');

        $validator
            ->add('public', 'valid', ['rule' => 'boolean'])
            ->requirePresence('public', 'create')
            ->notEmpty('public');

        $validator
            ->add('score', 'valid', ['rule' => 'decimal'])
            ->requirePresence('score', 'create')
            ->notEmpty('score');

        $validator
            ->add('intAlignmentAdjustment', 'decimalFormat', [
                'rule' => ['decimal', null]
            ])
            ->add('intAlignmentAdjustment', 'valueInRange', [
                'rule' => ['range', 0, 99.99]
            ])
            ->requirePresence('intAlignmentAdjustment', 'create');

        $validator
            ->add('intAlignmentThreshold', 'decimalFormat', [
                'rule' => ['decimal', null]
            ])
            ->add('intAlignmentThreshold', 'valueInRange', [
                'rule' => ['range', 0, 99.99]
            ])
            ->requirePresence('intAlignmentThreshold', 'create');

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
        $rules->add($rules->existsIn('local_area_id', 'LocalAreas'));
        $rules->add($rules->existsIn('parent_area_id', 'ParentAreas'));

        return $rules;
    }

    /**
     * Returns the count of consultants associated with a community
     *
     * @param int $communityId Community ID
     * @return int
     */
    public function getConsultantCount($communityId)
    {
        $result = $this->find('all')
            ->select(['Communities.id'])
            ->where(['Communities.id' => $communityId])
            ->contain([
                'Consultants' => function ($q) {
                    return $q->select(['Consultants.id']);
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
     *
     * @param int $consultantId Consultant user ID
     * @return array $communityId => $community_name
     */
    public function getConsultantCommunityList($consultantId)
    {
        $consultantsTable = TableRegistry::get('Consultants');
        $consultant = $consultantsTable->get($consultantId);
        if ($consultant->all_communities) {
            return $this->find('list')
                ->order(['Communities.name' => 'ASC']);
        }
        $result = $consultantsTable->find('all')
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
     *
     * @param int $communityId Community ID
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
     *
     * @param int $clientId Client user ID
     * @return array $communityId => $community_name
     */
    public function getClientCommunityList($clientId = null)
    {
        $joinTable = TableRegistry::get('ClientsCommunities');
        $query = $joinTable->find('list')
            ->select(['id' => 'community_id'])
            ->distinct(['community_id']);
        if ($clientId) {
            $query->where(['client_id' => $clientId]);
        } else {
            $usersTable = TableRegistry::get('Users');
            $clients = $usersTable->getClientList();
            $clientIds = array_keys($clients);

            /* Seems redundant, but helps keep this list clean
             * in the event of lingering "admin account was
             * temporarily a client account associated with this
             * community" situations. */
            $query->where(function ($exp, $q) use ($clientIds) {
                return $exp->in('client_id', $clientIds);
            });
        }

        $results = $query->toArray();
        if (! $results) {
            return [];
        }

        $communityIds = array_values($results);

        return $this->find('list')
            ->where(function ($exp, $q) use ($communityIds) {
                return $exp->in('id', $communityIds);
            })
            ->order(['Communities.name' => 'ASC'])
            ->toArray();
    }

    /**
     * Returns the ID of the (first) Community associated with the specified client, or NULL none found
     *
     * @param int|null $clientId Client user ID
     * @return int|null
     */
    public function getClientCommunityId($clientId)
    {
        if (empty($clientId)) {
            return null;
        }
        $communities = $this->getClientCommunityList($clientId);
        if (empty($communities)) {
            return null;
        }
        $communityIds = array_keys($communities);

        return $communityIds[0];
    }

    /**
     * Returns an arbitrary client ID associated with the selected community
     *
     * @param int $communityId Community ID
     * @return int
     */
    public function getCommunityClientId($communityId)
    {
        $result = $this->find('all')
            ->select(['id'])
            ->where(['Communities.id' => $communityId])
            ->contain([
                'Clients' => function ($q) {
                    return $q
                        ->autoFields(false)
                        ->select(['id'])
                        ->limit(1);
                }
            ])
            ->hydrate(false)
            ->first();

        return $result ? $result['clients'][0]['id'] : null;
    }

    /**
     * Returns an array of a community's clients
     *
     * @param int $communityId Community ID
     * @return array
     */
    public function getClients($communityId)
    {
        $result = $this->find('all')
            ->select(['id'])
            ->where(['id' => $communityId])
            ->contain([
                'Clients' => function ($q) {
                    return $q
                        ->select(['id', 'salutation', 'name', 'email'])
                        ->order(['Clients.name' => 'ASC']);
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
     * Returns the count of a community's clients
     *
     * @param int $communityId Community ID
     * @return int
     */
    public function getClientCount($communityId)
    {
        $clients = $this->getClients($communityId);

        return count($clients);
    }

    /**
     * Returns a terribly complex array used in the client home page that sums up progress information
     *
     * @param int $communityId Community ID
     * @param bool $isAdmin Current user is an administrator
     * @return array
     */
    public function getProgress($communityId, $isAdmin = false)
    {
        $productsTable = TableRegistry::get('Products');
        $surveysTable = TableRegistry::get('Surveys');
        $respondentsTable = TableRegistry::get('Respondents');
        $responsesTable = TableRegistry::get('Responses');
        $criteria = [];

        // Step 1
        $criteria[1]['client_assigned'] = [
            'At least one client account has been created for this community',
            $this->getClientCount($communityId) > 0
        ];

        $product = $productsTable->get(1);
        $criteria[1]['survey_purchased'] = [
            'Purchased ' . $product->description . ' ($' . number_format($product->price) . ')',
            $productsTable->isPurchased($communityId, 1)
        ];

        // If survey is not ready, put this at the end of step one
        // Otherwise, at the beginning of step two
        $surveyId = $surveysTable->getSurveyId($communityId, 'official');
        if ($surveyId) {
            $survey = $surveysTable->get($surveyId);
            $note = '<br />Questionnaire URL: <a href="' . $survey->sm_url . '">' . $survey->sm_url . '</a>';
        } else {
            $survey = null;
            $note = '';
        }
        $step = $surveyId ? 2 : 1;
        $criteria[$step]['survey_created'] = [
            'Leadership alignment assessment questionnaire has been prepared' . $note,
            (bool)$surveyId
        ];

        // Step 2
        $count = $surveyId ? $respondentsTable->getInvitedCount($surveyId) : 0;
        $note = $count ? " ($count " . __n('invitation', 'invitations', $count) . ' sent)' : '';
        $criteria[2]['invitations_sent'] = [
            'Community leaders have been sent questionnaire invitations' . $note,
            $surveyId && $count > 0
        ];

        $count = $surveyId ? $responsesTable->getDistinctCount($surveyId) : 0;
        $note = $count ? " ($count " . __n('response', 'responses', $count) . ' received)' : '';
        $criteria[2]['responses_received'] = [
            'Responses to the questionnaire have been collected' . $note,
            $surveyId && $count > 0
        ];

        $criteria[2]['response_threshold_reached'] = [
            'At least 25% of invited community leaders have responded to the questionnaire',
            $surveysTable->getInvitedResponsePercentage($surveyId) >= 25
        ];

        $hasUninvitedResponses = $surveysTable->hasUninvitedResponses($surveyId);
        $criteria[2]['hasUninvitedResponses'] = $hasUninvitedResponses;
        if ($hasUninvitedResponses) {
            $criteria[2]['unapproved_addressed'] = [
                'All unapproved responses have been approved or dismissed',
                ! $surveysTable->hasUnaddressedUnapprovedRespondents($surveyId)
            ];
        } else {
            $criteria[2]['unapproved_addressed'] = [
                'This questionnaire has no uninvited responses',
                true
            ];
        }

        $product = $productsTable->get(2);
        $criteria[2]['leadership_summit_purchased'] = [
            'Purchased optional ' . $product->description . ' ($' . number_format($product->price) . ')',
            $productsTable->isPurchased($communityId, 2)
        ];

        $community = $this->get($communityId);
        foreach (['a', 'b'] as $letter) {
            $date = $community->{"presentation_$letter"};
            $criteria[2]["presentation_{$letter}_scheduled"] = [
                'Scheduled Presentation ' . strtoupper($letter),
                $date != null
            ];
            $criteria[2]["presentation_{$letter}_completed"] = [
                'Completed Presentation ' . strtoupper($letter),
                $date ? ($date->format('Y-m-d') <= date('Y-m-d')) : false
            ];
        }

        $product = $productsTable->get(3);
        $criteria[2]['survey_purchased'] = [
            'Purchased ' . $product->description . ' ($' . number_format($product->price) . ')',
            $productsTable->isPurchased($communityId, 3)
        ];

        // Step 3
        $surveyId = $surveysTable->getSurveyId($communityId, 'organization');
        $survey = $surveyId ? $surveysTable->get($surveyId) : null;
        $surveyUrl = $survey ? $survey->sm_url : null;
        if ($surveyUrl) {
            $note = '<br />Questionnaire URL: <a href="' . $surveyUrl . '">' . $surveyUrl . '</a>';
        } else {
            $note = '';
        }
        $criteria[3]['survey_created'] = [
            'Community organization alignment assessment questionnaire has been prepared' . $note,
            (bool)$surveyId
        ];

        $count = $surveyId ? $respondentsTable->getInvitedCount($surveyId) : 0;
        $note = $count ? " ($count " . __n('invitation', 'invitations', $count) . ' sent)' : '';
        $criteria[3]['invitations_sent'] = [
            'Community organizations have been sent questionnaire invitations' . $note,
            $surveyId && $count > 0
        ];

        $count = $surveyId ? $responsesTable->getDistinctCount($surveyId) : 0;
        if ($count) {
            $note = " ($count " . __n('response', 'responses', $count) . ' received)';
        } else {
            $note = '';
        }
        $criteria[3]['responses_received'] = [
            'Responses to the questionnaire have been collected' . $note,
            $surveyId && $count > 0
        ];

        $criteria[3]['response_threshold_reached'] = [
            'At least 25% of invited community organizations have responded to the questionnaire',
            $surveysTable->getInvitedResponsePercentage($surveyId) >= 25
        ];

        $product = $productsTable->get(4);
        $criteria[3]['orgs_summit_purchased'] = [
            'Purchased optional ' . $product->description . ' ($' . number_format($product->price) . ')',
            $productsTable->isPurchased($communityId, 4)
        ];

        foreach (['c', 'd'] as $letter) {
            $date = $community->{"presentation_$letter"};
            $criteria[3]["presentation_{$letter}_scheduled"] = [
                'Scheduled Presentation ' . strtoupper($letter),
                $date != null
            ];
            $criteria[3]["presentation_{$letter}_completed"] = [
                'Completed Presentation ' . strtoupper($letter),
                $date ? ($date->format('Y-m-d') <= date('Y-m-d')) : false
            ];
        }

        $product = $productsTable->get(5);
        $description = 'Purchased ' . $product->description . ' ($' . number_format($product->price) . ')';
        $description = str_replace('PWRRR', 'PWR<sup>3</sup>', $description);
        $criteria[3]['policy_dev_purchased'] = [
            $description,
            $productsTable->isPurchased($communityId, 5)
        ];

        return $criteria;
    }

    /**
     * Removes all client associations from a community
     *
     * @param int $communityId Community ID
     * @return mixed
     */
    public function removeAllClientAssociations($communityId)
    {
        $community = $this->get($communityId);
        $joinTable = TableRegistry::get('ClientsCommunities');
        $clients = $joinTable->find('all')
            ->select(['id'])
            ->where(['community_id' => $communityId])
            ->toArray();

        return $this->Clients->unlink($community, $clients);
    }

    /**
     * Removes all consultant associations from a community
     *
     * @param int $communityId Community ID
     * @return mixed
     */
    public function removeAllConsultantAssociations($communityId)
    {
        $community = $this->get($communityId);
        $joinTable = TableRegistry::get('CommunitiesConsultants');
        $consultants = $joinTable->find('all')
            ->select(['id'])
            ->where(['community_id' => $communityId])
            ->toArray();

        return $this->Consultants->unlink($community, $consultants);
    }

    /**
     * A finder for /admin/communities/index
     *
     * @param \Cake\ORM\Query $query Query
     * @param array $options Options array
     * @return \Cake\ORM\Query
     */
    public function findAdminIndex(\Cake\ORM\Query $query, array $options)
    {
        $query
            ->contain([
                'Clients' => function ($q) {
                    return $q->select([
                        'Clients.email',
                        'Clients.name'
                    ]);
                },
                'OfficialSurvey' => function ($q) {
                    return $q->select([
                        'OfficialSurvey.id',
                        'OfficialSurvey.sm_id',
                        'OfficialSurvey.alignment_vs_local',
                        'OfficialSurvey.alignment_vs_parent',
                        'OfficialSurvey.respondents_last_modified_date',
                        'OfficialSurvey.active'
                    ]);
                },
                'OrganizationSurvey' => function ($q) {
                    return $q->select([
                        'OrganizationSurvey.id',
                        'OrganizationSurvey.sm_id',
                        'OrganizationSurvey.alignment_vs_local',
                        'OrganizationSurvey.alignment_vs_parent',
                        'OrganizationSurvey.respondents_last_modified_date',
                        'OrganizationSurvey.active'
                    ]);
                },
                'ParentAreas' => function ($q) {
                    return $q->select(['ParentAreas.name']);
                }
            ])
            ->group('Communities.id')
            ->select([
                'Communities.id',
                'Communities.name',
                'Communities.score',
                'Communities.created'
            ]);

        return $query;
    }

    /**
     * A finder for /admin/reports/index
     *
     * @param \Cake\ORM\Query $query Query
     * @param array $options Options array
     * @return \Cake\ORM\Query
     */
    public function findForReport(\Cake\ORM\Query $query, array $options)
    {
        $dateThreshold = new Date('-30 days');
        $query
            ->select([
                'id',
                'name',
                'score',
                'presentation_a',
                'presentation_b',
                'presentation_c',
                'notes'
            ])
            ->where(['dummy' => 0])
            ->contain([
                'ParentAreas' => function ($q) {
                    return $q->select(['id', 'name', 'fips']);
                },
                'OfficialSurvey' => function ($q) {
                    return $q->select(['id', 'alignment_vs_local', 'alignment_vs_parent']);
                },
                'OrganizationSurvey' => function ($q) {
                    return $q->select(['id', 'alignment_vs_local', 'alignment_vs_parent']);
                },
                'ActivityRecords' => function ($q) use ($dateThreshold) {
                    return $q
                        ->where(function ($exp, $q) use ($dateThreshold) {
                            return $exp->gte('ActivityRecords.created', $dateThreshold);
                        })
                        ->order(['ActivityRecords.created' => 'DESC']);
                },
            ])
            ->order(['Communities.name' => 'ASC']);

        return $query;
    }

    /**
     * Returns dummy community IDs
     *
     * @return array
     */
    public function getDummyCommunityIds()
    {
        $communities = $this->find('all')
            ->select(['id'])
            ->where(['dummy' => 1])
            ->toArray();

        return Hash::extract($communities, '{n}.id');
    }
}
