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
        $rules->add($rules->existsIn('local_area_id', 'LocalAreas'));
        $rules->add($rules->existsIn('parent_area_id', 'ParentAreas'));
        return $rules;
    }

    /**
     * @param int $communityId
     * @return int
     */
    public function getParentAreaId($communityId)
    {
        $community = $this->get($communityId);
        return $community->parent_area_id;
    }

    /**
     * @param int $communityId
     * @return GoogleCharts
     */
    public function getPwrBarChart($communityId)
    {
        $areaId = $this->getParentAreaId($communityId);
        $areasTable = TableRegistry::get('Areas');
        return $areasTable->getPwrBarChart($areaId);
    }

    /**
     * @param int $communityId
     * @return array
     */
    public function getPwrTable($communityId)
    {
        $areaId = $this->getParentAreaId($communityId);
        $areasTable = TableRegistry::get('Areas');
        return $areasTable->getPwrTable($areaId);
    }

    /**
     * @param int $communityId
     * @return GoogleCharts
     */
    public function getEmploymentLineChart($communityId)
    {
        $areaId = $this->getParentAreaId($communityId);
        $areasTable = TableRegistry::get('Areas');
        return $areasTable->getEmploymentLineChart($areaId);
    }

    /**
     * @param int $communityId
     * @return array
     */
    public function getEmploymentGrowthTableData($communityId)
    {
        $areaId = $this->getParentAreaId($communityId);
        $areasTable = TableRegistry::get('Areas');
        return $areasTable->getEmploymentGrowthTableData($areaId);
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
     * @param int $consultant_id
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

            /* Seems redundant, but helps clean this list clean in the event of lingering
             * "admin account was temporarily a client account associated with this community"
             * situations. */
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
            ->toArray();
    }

    /**
     * Returns the ID of the (first) Community associated with the specified client, or NULL if no such community is found.
     * @param int|null $clientId
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
     * @param int $communityId
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
     * @param int $communityId
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
        $productsTable = TableRegistry::get('Products');
        $surveysTable = TableRegistry::get('Surveys');
        $respondentsTable = TableRegistry::get('Respondents');
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
            $note = '<br />Survey URL: <a href="'.$survey->sm_url.'">'.$survey->sm_url.'</a>';
        } else {
            $note =  '';
        }
        $step = $surveyId ? 2 : 1;
        $criteria[$step]['survey_created'] = [
            'Leadership alignment assessment survey has been prepared'.$note,
            (boolean) $surveyId
        ];


        // Step 2
        $count = $surveyId ? $respondentsTable->getInvitedCount($surveyId) : 0;
        $note = $count ? " ($count ".__n('invitation', 'invitations', $count).' sent)' : '';
        $criteria[2]['invitations_sent'] = [
            'Community leaders have been sent survey invitations'.$note,
            $surveyId && $count > 0
        ];

        $responsesTable = TableRegistry::get('Responses');
        $count = $surveyId ? $responsesTable->getDistinctCount($surveyId) : 0;
        $note = $count ? " ($count ".__n('response', 'responses', $count).' received)' : '';
        $criteria[2]['responses_received'] = [
            'Responses to the survey have been collected'.$note,
            $surveyId && $count > 0
        ];

        $criteria[2]['response_threshhold_reached'] = [
            'At least 50% of invited community leaders have responded to the survey',
            $surveysTable->getInvitedResponsePercentage($surveyId) >= 50
        ];

        if ($surveysTable->hasUninvitedResponses($surveyId)) {
            $criteria[2]['unapproved_addressed'] = [
                'All unapproved responses have been approved or dismissed',
                ! $surveysTable->hasUnaddressedUnapprovedRespondents($surveyId)
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
        $purchasedLeadershipSummit = $productsTable->isPurchased($communityId, 2);
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
                $productsTable->isPurchased($communityId, 3)
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
                $productsTable->isPurchased($communityId, 3)
            ];
        }


        // Step 3
        $surveyId = $surveysTable->getSurveyId($communityId, 'organization');
        $survey = $surveyId ? $surveysTable->get($surveyId) : null;
        $surveyUrl = $survey ? $survey->sm_url : null;
        $note = $surveyUrl ? '<br />Survey URL: <a href="'.$surveyUrl.'">'.$surveyUrl.'</a>' : '';
        $criteria[3]['survey_created'] = [
            'Community organization alignment assessment survey has been prepared'.$note,
            (boolean) $surveyId
        ];

        $count = $surveyId ? $respondentsTable->getCount($surveyId) : 0;
        $note = $count ? " ($count ".__n('response', 'responses', $count).' received)' : '';
        $criteria[3]['responses_received'] = [
            'Responses to the survey have been collected'.$note,
            $surveyId && $count > 0
        ];

        $criteria[3]['alignment_calculated'] = [
            'Community organization alignment calculated',
            $survey && $survey->alignment_passed != 0
        ];

        if ($survey && $survey->alignment_passed == 0) {
            $note = ' (alignment not yet calculated)';
        } elseif ($isAdmin) {
            $note = " ({$survey->alignment}% aligned)";
        } else {
            $note = '';
        }
        $purchasedCommunitySummit = $productsTable->isPurchased($communityId, 4);
        if (! $purchasedCommunitySummit) {
            $criteria[3]['alignment_passed'] = [
                'Passed community alignment assessment'.$note,
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
                'Passed community alignment assessment'.$note,
                 $survey->alignment_passed == 1
            ];

            $criteria['3.5']['policy_dev_purchased'] = [
                'Purchased PwR3 Policy Development ($5,000)',
                $productsTable->isPurchased($communityId, 5)
            ];
        }


        // Step 4
        $community = $this->get($communityId);
        $note = $community->town_meeting_date ? ' ('.date('F jS, Y', strtotime($community->town_meeting_date)).')': '';
        $criteria[4]['meeting_scheduled'] = [
            'Scheduled town meeting'.$note,
            $community->town_meeting_date != null
        ];
        if ($community->town_meeting_date != null) {
            $criteria[4]['meeting_held'] = [
                'Held town meeting',
                $community->town_meeting_date <= date('Y-m-d')
            ];
        }

        return $criteria;
    }

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

    public function getSpreadsheetObject($communities)
    {
        // Start up
        require_once(ROOT.DS.'vendor'.DS.'phpoffice'.DS.'phpexcel'.DS.'Classes'.DS.'PHPExcel.php');
        \PHPExcel_Cell::setValueBinder(new \PHPExcel_Cell_AdvancedValueBinder());
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        // Metadata
        $title = 'CRI Project Overview - '.date('F j, Y');
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
            'Officials Survey created',
            'Officials Survey alignment',
            'Officials Survey passed',
            'Organizations Survey created',
            'Organizations Survey alignment',
            'Organizations Survey passed'
        ];
        $lastCol = 'B';
        foreach ($columnTitles as $col => $colTitle) {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col + 1, 2, $colTitle);
            $lastCol++; // Huh. Turns out this works.
        }
        $objPHPExcel->getActiveSheet()->getStyle('B2:'.$lastCol.'2')->applyFromArray([
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
                    case 'Officials Survey created':
                        $value = isset($community->official_survey['sm_id']) ? 'Yes' : 'No';
                        break;
                    case 'Officials Survey alignment':
                        $value = $community->official_survey['alignment'] ? $community->official_survey['alignment'].'%' : 'Not set';
                        break;
                    case 'Officials Survey passed':
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
                    case 'Organizations Survey created':
                        $value = isset($community->organization_survey['sm_id']) ? 'Yes' : 'No';
                        break;
                    case 'Organizations Survey alignment':
                        $value = $community->organization_survey['alignment'] ? $community->organization_survey['alignment'].'%' : 'Not set';
                        break;
                    case 'Organizations Survey passed':
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
}
