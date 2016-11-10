<?php
namespace App\Model\Table;

use App\Model\Entity\Community;
use Cake\ORM\Query;
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
            ->add('fast_track', 'valid', ['rule' => 'boolean'])
            ->requirePresence('fast_track', 'create')
            ->notEmpty('fast_track');

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
            ->add('intAlignmentThreshhold', 'decimalFormat', [
                'rule' => ['decimal', null]
            ])
            ->add('intAlignmentThreshhold', 'valueInRange', [
                'rule' => ['range', 0, 99.99]
            ])
            ->requirePresence('intAlignmentThreshhold', 'create');

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

        $criteria[1]['survey_purchased'] = [
            'Purchased Community Leadership Alignment Assessment ($3,500)',
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

        $criteria[2]['response_threshhold_reached'] = [
            'At least 25% of invited community leaders have responded to the questionnaire',
            $surveysTable->getInvitedResponsePercentage($surveyId) >= 25
        ];

        if ($surveysTable->hasUninvitedResponses($surveyId)) {
            $criteria[2]['unapproved_addressed'] = [
                'All unapproved responses have been approved or dismissed',
                ! $surveysTable->hasUnaddressedUnapprovedRespondents($surveyId)
            ];
        }

        $criteria[2]['alignment_calculated'] = [
            'Community leadership alignment calculated',
            $survey && $survey->alignment_passed != 0
        ];

        if ($survey && $survey->alignment_passed == 0) {
            $note = ' (alignment not yet calculated)';
        } elseif ($survey && $isAdmin) {
            $alignment = $survey->alignment;
            $note = " ($alignment% aligned)";
        } else {
            $note = '';
        }
        $purchasedLeadershipSummit = $productsTable->isPurchased($communityId, 2);
        if (! $purchasedLeadershipSummit) {
            $criteria[2]['alignment_passed'] = [
                'Passed leadership alignment assessment' . $note,
                $survey && $survey->alignment_passed == 1
            ];
        }

        if (($survey && $survey->alignment_passed == -1) || $purchasedLeadershipSummit) {
            $criteria[2]['consultant_assigned'] = [
                'At least one consultant has been assigned to this community',
                $this->getConsultantCount($communityId) > 0
            ];

            $criteria[2]['summit_purchased'] = [
                'Purchased Leadership Summit ($1,500)',
                $purchasedLeadershipSummit
            ];
        }

        if (! $purchasedLeadershipSummit && $survey && ($survey->alignment_passed == 0 || $survey->alignment_passed == 1)) {
            $criteria[2]['survey_purchased'] = [
                'Purchased Community Organizations Alignment Assessment ($3,500)',
                $productsTable->isPurchased($communityId, 3)
            ];
        }


        // Step 2.5
        if ($purchasedLeadershipSummit) {
            $criteria['2.5']['alignment_passed'] = [
                'Passed leadership alignment assessment' . $note,
                $survey && $survey->alignment_passed == 1
            ];
        }

        if (($survey && $survey->alignment_passed == -1) || $purchasedLeadershipSummit) {
            $criteria['2.5']['survey_purchased'] = [
                'Purchased Community Organizations Alignment Assessment ($3,500)',
                $productsTable->isPurchased($communityId, 3)
            ];
        }


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

        $criteria[3]['alignment_calculated'] = [
            'Community organization alignment calculated',
            $survey && $survey->alignment_passed != 0
        ];

        if ($survey && $survey->alignment_passed == 0) {
            $note = ' (alignment not yet calculated)';
        } elseif ($survey && $isAdmin) {
            $note = " ({$survey->alignment}% aligned)";
        } else {
            $note = '';
        }
        $purchasedCommunitySummit = $productsTable->isPurchased($communityId, 4);
        if (! $purchasedCommunitySummit) {
            $criteria[3]['alignment_passed'] = [
                'Passed community alignment assessment' . $note,
                 $survey && $survey->alignment_passed == 1
            ];
        }

        if (($survey && $survey->alignment_passed == -1) || $purchasedCommunitySummit) {
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
                $productsTable->isPurchased($communityId, 5)
            ];
        }


        // Step 3.5
        if ($purchasedCommunitySummit) {
            $criteria['3.5']['alignment_passed'] = [
                'Passed community alignment assessment' . $note,
                $survey && $survey->alignment_passed == 1
            ];

            $criteria['3.5']['policy_dev_purchased'] = [
                'Purchased PwR3 Policy Development ($5,000)',
                $productsTable->isPurchased($communityId, 5)
            ];
        }

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
     * Returns a PHPExcel spreadsheet object
     *
     * @param array $communities Array of community entities
     * @return \PHPExcel
     * @throws \PHPExcel_Exception
     */
    public function getSpreadsheetObject($communities)
    {
        $objPHPExcel = $this->getPhpExcelObject();

        // Metadata
        $title = 'CRI Project Overview - ' . date('F j, Y');
        $author = 'Center for Business and Economic Research, Ball State University';
        $objPHPExcel->getProperties()
            ->setCreator($author)
            ->setLastModifiedBy($author)
            ->setTitle($title)
            ->setSubject($title)
            ->setDescription('');
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(11);

        // Title
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, $title);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 24
            ]
        ]);

        // Headers
        $columnTitles = [
            'Community',
            'Area',
            'Stage',
            'Fast Track',
            'Officials Questionnaire created',
            'Officials Questionnaire alignment',
            'Officials Questionnaire passed',
            'Organizations Questionnaire created',
            'Organizations Questionnaire alignment',
            'Organizations Questionnaire passed'
        ];
        $lastCol = 'B';
        foreach ($columnTitles as $col => $colTitle) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col + 1, 2, $colTitle);
            $lastCol++; // Huh. Turns out this works.
        }
        $objPHPExcel->getActiveSheet()->getStyle('B2:' . $lastCol . '2')->applyFromArray([
            'font' => [
                'bold' => true
            ]
        ]);

        // Data
        foreach ($communities as $ri => $community) {
            $row = $ri + 3;
            foreach ($columnTitles as $ci => $col) {
                $value = null;
                switch ($col) {
                    case 'Community':
                        $value = $community->name;
                        break;
                    case 'Area':
                        $value = $community->parent_area['name'];
                        break;
                    case 'Stage':
                        $value = $community->score;
                        break;
                    case 'Fast Track':
                        $value = $community->fast_track ? 'Yes' : 'No';
                        break;
                    case 'Officials Questionnaire created':
                        $value = isset($community->official_survey['sm_id']) ? 'Yes' : 'No';
                        break;
                    case 'Officials Questionnaire alignment':
                        if ($community->official_survey['alignment']) {
                            $value = $community->official_survey['alignment'] . '%';
                        } else {
                            $value = 'Not set';
                        }
                        break;
                    case 'Officials Questionnaire passed':
                        $passed = $community->official_survey['alignment_passed'];
                        switch ($passed) {
                            case 1:
                                $value = 'Yes';
                                break;
                            case -1:
                                $value = 'No';
                                break;
                            case 0:
                                $value = 'TBD';
                                break;
                        }
                        break;
                    case 'Organizations Questionnaire created':
                        $value = isset($community->organization_survey['sm_id']) ? 'Yes' : 'No';
                        break;
                    case 'Organizations Questionnaire alignment':
                        if ($community->organization_survey['alignment']) {
                            $value = $community->organization_survey['alignment'] . '%';
                        } else {
                            $value = 'Not set';
                        }
                        break;
                    case 'Organizations Questionnaire passed':
                        $passed = $community->organization_survey['alignment_passed'];
                        switch ($passed) {
                            case 1:
                                $value = 'Yes';
                                break;
                            case -1:
                                $value = 'No';
                                break;
                            case 0:
                                $value = 'TBD';
                                break;
                        }
                        break;
                }
                $col = $ci + 1;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value);
            }
        }

        // Reduce the width of the first column
        //   (which contains only the title and will overflow over the unoccupied cells to the right)
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(1.5);

        // Automatically adjust the width of all columns AFTER the first
        $lastCol = count($columnTitles);
        $colLetter = 'B';
        for ($c = 1; $c <= $lastCol; $c++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($colLetter)->setAutoSize(true);
            $colLetter++;
        }

        return $objPHPExcel;
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
                        'OfficialSurvey.alignment',
                        'OfficialSurvey.alignment_passed',
                        'OfficialSurvey.respondents_last_modified_date'
                    ]);
                },
                'OrganizationSurvey' => function ($q) {
                    return $q->select([
                        'OrganizationSurvey.id',
                        'OrganizationSurvey.sm_id',
                        'OrganizationSurvey.alignment',
                        'OrganizationSurvey.alignment_passed',
                        'OrganizationSurvey.respondents_last_modified_date'
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
                'Communities.fast_track',
                'Communities.score',
                'Communities.created'
            ]);
        return $query;
    }

    public function getReport()
    {
        $report = [];

        $communities = $this->find('all')
            ->select([
                'id', 'name', 'score'
            ])
            ->contain([
                'ParentAreas' => function($q) {
                    return $q->select(['id', 'name', 'fips']);
                },
                'OfficialSurvey' => function($q) {
                    return $q->select(['id', 'alignment']);
                },
                'OrganizationSurvey' => function($q) {
                    return $q->select(['id', 'alignment']);
                }
            ])
            ->order(['Communities.name' => 'ASC']);

        $respondentsTable = TableRegistry::get('Respondents');
        $respondents = $respondentsTable->find('all')
            ->select(['id', 'approved', 'invited', 'survey_id'])
            ->contain([
                'Responses' => function ($q) {
                    return $q->select(['id', 'respondent_id']);
                }
            ])
            ->toArray();
        $respondents = Hash::combine($respondents, '{n}.id', '{n}', '{n}.survey_id');

        $responsesTable = TableRegistry::get('Responses');
        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        foreach ($communities as $community) {
            // Collect general information about this community
            $report[$community->id] = [
                'name' => $community->name,
                'parentArea' => $community->parent_area->name,
                'parentAreaFips' => $community->parent_area->fips,
                'presentationsGiven' => [
                    'a' => 'No',
                    'b' => 'No',
                    'c' => 'No'
                ]
            ];

            // Collect information about survey responses and alignment
            $surveyTypes = [
                'official_survey' => $community->official_survey,
                'organization_survey' => $community->organization_survey,
            ];
            foreach ($surveyTypes as $key => $survey) {
                $invitationCount = 0;
                $approvedResponseCount = 0;
                $responseRate = 'N/A';
                if ($survey && isset($respondents[$survey->id])) {
                    foreach ($respondents[$survey->id] as $respondent) {
                        if ($respondent->invited) {
                            $invitationCount++;
                        }
                        if ($respondent->approved && ! empty($respondent->responses)) {
                            $approvedResponseCount++;
                        }
                    }
                    if ($invitationCount) {
                        $responseRate = round(($approvedResponseCount / $invitationCount) * 100) . '%';
                    } else {
                        $responseRate = 'N/A';
                    }
                }

                // Format and sum internal alignment
                $internalAlignment = [];
                if ($survey) {
                    $internalAlignment = $responsesTable->getInternalAlignmentPerSector($survey->id);
                    if ($internalAlignment) {
                        foreach ($internalAlignment as $sector => &$value) {
                            $value = round($value, 1);
                        }
                        $internalAlignment['total'] = array_sum($internalAlignment);
                    }
                }
                if (! $internalAlignment) {
                    $internalAlignment = array_combine($sectors, [null, null, null, null, null]);
                    $internalAlignment['total'] = null;
                }

                // Determine status
                $correspondingStep = ($key == 'official_survey') ? 2 : 3;
                if ($community->score < $correspondingStep) {
                    $status = 'Not started yet';
                } elseif ($community->score < ($correspondingStep + 1)) {
                    $status = 'In progress';
                } else {
                    $status = 'Complete';
                }

                // PWRRR alignment
                if ($survey) {
                    $alignment = $survey->alignment ? $survey->alignment . '%' : null;
                    $alignmentCalculated = $survey->alignment ? 'Yes' : 'No';
                } else {
                    $alignment = null;
                    $alignmentCalculated = 'No';
                }

                $report[$community->id][$key] = [
                    'invitations' => $invitationCount,
                    'responses' => $approvedResponseCount,
                    'responseRate' => $responseRate,
                    'alignment' => $alignment,
                    'alignmentCalculated' => $alignmentCalculated,
                    'internalAlignment' => $internalAlignment,
                    'status' => $status
                ];
            }
        }

        return $report;
    }

    /**
     * Returns a PHPExcel object for the OCRA version of the "all communities" report
     *
     * @return \PHPExcel
     */
    public function getOcraReportSpreadsheet()
    {
        $report = $this->getReport();
        $objPHPExcel = $this->getPhpExcelObject();

        // Metadata
        $title = 'CRI Report for OCRA - ' . date('F j, Y');
        $author = 'Center for Business and Economic Research, Ball State University';
        $objPHPExcel->getProperties()
            ->setCreator($author)
            ->setLastModifiedBy($author)
            ->setTitle($title)
            ->setSubject($title)
            ->setDescription('');
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(11);

        // Set up column headers
        $columnTitles = [
            'Community',
            'Area',
            'Area FIPS'
        ];
        $surveyColumnHeaders = [];
        foreach (['officials', 'organizations'] as $surveyType) {
            $surveyColumnHeaders[$surveyType] = [
                'Invitations',
                'Responses',
                'Completion Rate',
                'Alignment Calculated',
            ];
            if ($surveyType == 'officials') {
                $surveyColumnHeaders[$surveyType][] = 'Presentation A Given';
                $surveyColumnHeaders[$surveyType][] = 'Presentation B Given';
            } else {
                $surveyColumnHeaders[$surveyType][] = 'Presentation C Given';
            }
            $surveyColumnHeaders[$surveyType][] = 'Status';
        }
        $generalColCount = count($columnTitles);
        $officialsColCount = count($surveyColumnHeaders['officials']);
        $orgsColCount = count($surveyColumnHeaders['organizations']);

        // Title
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, $title);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 24
            ]
        ]);
        $span = 'A1:' . $this->getColumnKey($generalColCount + $officialsColCount + $orgsColCount - 1) . '1';
        $objPHPExcel->getActiveSheet()->mergeCells($span);

        // Header: Survey groups
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($generalColCount, 2, 'Community Leadership');
        $span =
            $this->getColumnKey($generalColCount) . '2:' .
            $this->getColumnKey($generalColCount + $officialsColCount - 1) . '2';
        $objPHPExcel->getActiveSheet()->mergeCells($span);
        $objPHPExcel->getActiveSheet()->getStyle($span)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'top' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                'left' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                'right' => ['style' => \PHPExcel_Style_Border::BORDER_THIN]
            ]
        ]);

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($generalColCount + $officialsColCount, 2, 'Community Organizations');
        $span =
            $this->getColumnKey($generalColCount + $officialsColCount) . '2:' .
            $this->getColumnKey($generalColCount + $officialsColCount + $orgsColCount - 1) . '2';
        $objPHPExcel->getActiveSheet()->mergeCells($span);
        $objPHPExcel->getActiveSheet()->getStyle($span)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'top' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                'left' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                'right' => ['style' => \PHPExcel_Style_Border::BORDER_THIN]
            ]
        ]);

        // Header: Column titles
        $columnTitles = array_merge($columnTitles, $surveyColumnHeaders['officials']);
        $columnTitles = array_merge($columnTitles, $surveyColumnHeaders['organizations']);
        foreach ($columnTitles as $col => $colTitle) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 3, $colTitle);
        }
        $lastCol = $this->getColumnKey($generalColCount + $officialsColCount + $orgsColCount - 1);
        $span = "A3:{$lastCol}3";
        $objPHPExcel->getActiveSheet()->getStyle($span)->applyFromArray([
            'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'bottom' => ['style' => \PHPExcel_Style_Border::BORDER_THIN]
            ],
            'font' => ['bold' => true]
        ]);
        $span = 'A3:' . $this->getColumnKey($generalColCount - 1) . '3';
        $objPHPExcel->getActiveSheet()->getStyle($span)->applyFromArray([
            'borders' => [
                'left' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                'top' => ['style' => \PHPExcel_Style_Border::BORDER_THIN]
            ]
        ]);
        $span =
            $this->getColumnKey($generalColCount) . '3:' .
            $this->getColumnKey($generalColCount + $officialsColCount - 1) . '3';
        $objPHPExcel->getActiveSheet()->getStyle($span)->applyFromArray([
            'borders' => [
                'left' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                'right' => ['style' => \PHPExcel_Style_Border::BORDER_THIN]
            ]
        ]);
        $span =
            $this->getColumnKey($generalColCount + $officialsColCount) . '3:' .
            $this->getColumnKey($generalColCount + $officialsColCount + $orgsColCount - 1) . '3';
        $objPHPExcel->getActiveSheet()->getStyle($span)->applyFromArray([
            'borders' => [
                'left' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                'right' => ['style' => \PHPExcel_Style_Border::BORDER_THIN]
            ]
        ]);

        // Data
        $row = 3;
        foreach ($report as $community) {
            $row++;
            $cells = [
                $community['name'],
                $community['parentArea'],
                $community['parentAreaFips']
            ];
            foreach (['official_survey', 'organization_survey'] as $surveyType) {
                $survey = $community[$surveyType];
                $cells[] = $survey['invitations'];
                $cells[] = $survey['responses'];
                $cells[] = $survey['responseRate'];
                $cells[] = $survey['alignmentCalculated'];
                if ($surveyType == 'official_survey') {
                    $cells[] = $community['presentationsGiven']['a'];
                    $cells[] = $community['presentationsGiven']['b'];
                } else {
                    $cells[] = $community['presentationsGiven']['c'];
                }
                $cells[] = $survey['status'];
            }
            foreach ($cells as $col => $value) {
                if (strpos($value, '%') === false) {
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value);
                } else {
                    $cell = $this->getColumnKey($col) . $row;
                    $objPHPExcel->getActiveSheet()->getCell($cell)->setValueExplicit(
                        $value,
                        \PHPExcel_Cell_DataType::TYPE_STRING
                    );
                }
            }
        }
        $span = "A4:{$lastCol}{$row}";
        $objPHPExcel->getActiveSheet()->getStyle($span)->applyFromArray([
            'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT],
            'borders' => [
                'outline' => ['style' => \PHPExcel_Style_Border::BORDER_THIN]
            ]
        ]);
        $span =
            $this->getColumnKey($generalColCount) . '4:'.
            $this->getColumnKey($generalColCount) . $row;
        $objPHPExcel->getActiveSheet()->getStyle($span)->applyFromArray([
            'borders' => [
                'left' => ['style' => \PHPExcel_Style_Border::BORDER_THIN]
            ]
        ]);
        $span =
            $this->getColumnKey($generalColCount + $officialsColCount) . '4:'.
            $this->getColumnKey($generalColCount + $officialsColCount) . $row;
        $objPHPExcel->getActiveSheet()->getStyle($span)->applyFromArray([
            'borders' => [
                'left' => ['style' => \PHPExcel_Style_Border::BORDER_THIN]
            ]
        ]);

        // Adjust the width of all columns AFTER the first
        $totalColCount = $generalColCount + $officialsColCount + $orgsColCount;
        for ($n = 0; $n < $totalColCount; $n++) {
            $colLetter = $this->getColumnKey($n);
            $objPHPExcel->getActiveSheet()->getColumnDimension($colLetter)->setAutoSize(true);
        }

        return $objPHPExcel;
    }

    /**
     * Returns an initialized PHPExcel object
     *
     * @return \PHPExcel
     */
    public function getPhpExcelObject()
    {
        require_once ROOT . DS . 'vendor' . DS . 'phpoffice' . DS . 'phpexcel' . DS . 'Classes' . DS . 'PHPExcel.php';
        \PHPExcel_Cell::setValueBinder(new \PHPExcel_Cell_AdvancedValueBinder());
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        return $objPHPExcel;
    }

    /**
     * Returns the nth Excel-style column key (A, B, C, ... AA, AB, etc.)
     *
     * @param int $num
     * @return string
     */
    public function getColumnKey($num)
    {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->getColumnKey($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }
}
