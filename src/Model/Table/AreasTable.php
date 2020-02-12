<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Areas Model
 *
 * @property \Cake\ORM\Association\HasMany $Statistic
 * @property \App\Model\Table\StatisticsTable|\Cake\ORM\Association\HasMany $Statistics
 * @method \App\Model\Entity\Area get($primaryKey, $options = [])
 * @method \App\Model\Entity\Area newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Area[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Area|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Area patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Area[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Area findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AreasTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->setTable('areas');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->hasMany('Statistics', [
            'foreignKey' => 'area_id',
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
            ->add('fips', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('fips');

        $validator
            ->add('production_rank', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('production_rank');

        $validator
            ->add('wholesale_rank', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('wholesale_rank');

        $validator
            ->add('retail_rank', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('retail_rank');

        $validator
            ->add('residential_rank', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('residential_rank');

        $validator
            ->add('recreation_rank', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('recreation_rank');

        return $validator;
    }

    /**
     * Returns a GoogleCharts object for a PWRRR bar chart for an area, or false if data or area is unavailable
     *
     * @param int|null $areaId Area ID
     * @return \GoogleCharts|bool
     */
    public function getPwrBarChart($areaId)
    {
        if (! $areaId) {
            return false;
        }

        // Determine what statistic categories (stat-cats?) will be used
        $statCategoriesTable = TableRegistry::get('StatCategories');
        $groups = $statCategoriesTable->getGroups();
        $allRelevantCatIds = [];
        foreach ($groups as $groupName => $groupCatIds) {
            $allRelevantCatIds = array_merge($allRelevantCatIds, $groupCatIds);
        }

        $area = $this->find('all')
            ->select(['Areas.id'])
            ->where(['Areas.id' => $areaId])
            ->contain([
                'Statistics' => function ($q) use ($allRelevantCatIds) {
                    return $q
                        ->where(function ($exp, $q) use ($allRelevantCatIds) {
                            return $exp->in('Statistics.stat_category_id', $allRelevantCatIds);
                        })
                        ->contain(['StatCategories']);
                },
            ])
            ->first();
        if (empty($area) || empty($area->statistics)) {
            return false;
        }

        // Initialize chart
        $chart = $this->getGoogleChartsObject();
        $chart->type('ComboChart');

        /* To have each group of bars share one color, each group needs to constitute its own series.
         * Each row is added like this:
         *      Production row: #, #, 0, 0, 0, 0, 0, ...
         *      Wholesale row:  0, 0, #, #, #, 0, 0, ...
         *      Retail row:     0, 0, 0, 0, 0, #, #, ... */
        $columns = [
            'category' => [
                'type' => 'string',
                'label' => 'Category',
            ],
            'score_production' => [
                'type' => 'number',
                'label' => 'Production',
            ],
            'score_wholesale' => [
                'type' => 'number',
                'label' => 'Wholesale',
            ],
            'score_retail' => [
                'type' => 'number',
                'label' => 'Retail',
            ],
            'score_residential' => [
                'type' => 'number',
                'label' => 'Residential',
            ],
            'score_recreation' => [
                'type' => 'number',
                'label' => 'Recreation',
            ],
            'average' => [
                'type' => 'number',
                'label' => 'National average',
            ],
            'certainty' => [
                'type' => 'boolean',
                'role' => 'certainty',
                'label' => 'Certainty',
            ],
            'annotation' => [
                'type' => 'string',
                'role' => 'annotation',
                'label' => 'Annotation',
            ],
        ];
        $chart->columns($columns);

        $columnGroups = array_keys($columns);
        // Remove the 'category' key
        $columnGroups = array_slice($columnGroups, 1);

        // Start collecting all values so the min and max can be determined
        $allValues = [];

        // Add the 'national average line' at the beginning and (later) the end of the chart
        $firstRow = ['category' => ''];
        foreach ($columnGroups as $columnGroup) {
            $firstRow[$columnGroup] = 0;
        }
        $firstRow['average'] = 1;
        $firstRow['certainty'] = 'false';
        $firstRow['annotation'] = '';
        $chart->addRow($firstRow);

        foreach ($area['statistics'] as $i => $stat) {
            $categoryId = $stat['stat_category_id'];
            $row = [];
            foreach ($columnGroups as $columnGroup) {
                $groupName = str_replace('score_', '', $columnGroup);
                if (! isset($groups[$groupName])) {
                    continue;
                }
                $groupCatIds = $groups[$groupName];
                if (in_array($categoryId, $groupCatIds)) {
                    $value = $stat['value'];
                    $allValues[] = $value;
                } else {
                    $value = 0;
                }
                $row[$columnGroup] = $value;
            }
            $row['category'] = $stat['stat_category']['name'];
            $row['average'] = 1;
            $row['certainty'] = 'false';

            // Place an annotation above the second column
            $row['annotation'] = $i == 1 ? 'National Average' : null;

            $chart->addRow($row);
        }

        // Add the national average line also to the end of the graph
        $lastRow = $firstRow;
        $lastRow['annotation'] = '';
        $chart->addRow($lastRow);

        // Determine the min and max values of the vertical axis based on the min and max values
        $maxValue = max($allValues);
        $axisMax = ceil($maxValue * 2) / 2;
        $minValue = min($allValues);
        $axisMin = floor($minValue * 2) / 2;

        /* Determine what ticks are marked on the vertical axis
         * A maximum of 14 ticks can fit on the axis without overlapping,
         * so space those every 0.5 if they will fit or fall back on
         * default tick marking otherwise.
         */
        if ($axisMax - $axisMin <= 14) {
            $ticks = range($axisMin, $axisMax, 0.5);
        } else {
            $ticks = null;
        }

        $chart->options([
            'bar' => [
                'groupWidth' => '95%',
            ],
            'chartArea' => [
                'width' => '90%',
                'height' => '80%',
            ],
            'hAxis' => [
                'textPosition' => 'none',
                'viewWindow' => [
                    'min' => 1,
                    'max' => count($area['statistics']) + 1,
                ],
            ],
            'isStacked' => true,
            'legend' => 'bottom',
            'series' => [
                [
                    'color' => '#ce845f',
                    'targetAxisIndex' => 1,
                ],
                ['color' => '#d9bc7b'],
                ['color' => '#a8b28a'],
                ['color' => '#8baebc'],
                ['color' => '#a6ccc6'],
                [
                    'color' => '#cc0022',
                    'lineWidth' => 1,
                    'type' => 'line',
                    'visibleInLegend' => false,
                ],
            ],
            'seriesType' => 'bars',
            'titlePosition' => 'none',
            'vAxis' => [
                'maxValue' => $axisMax,
                'minValue' => $axisMin,
                'viewWindow' => [
                    'max' => $axisMax + 0.5,
                    'min' => $axisMin - 0.5,
                ],
                'ticks' => $ticks,
                'format' => '#.0',
            ],
            'width' => 725,
        ]);

        return $chart;
    }

    /**
     * Returns an array used to create a PWRRR table for an area, or false if data or area is unavailable
     *
     * @param int|null $areaId Area ID
     * @return array|bool
     */
    public function getPwrTable($areaId)
    {
        if (! $areaId) {
            return false;
        }

        // Determine what statistic categories (stat-cats?) will be used
        $statCategoriesTable = TableRegistry::get('StatCategories');
        $groups = $statCategoriesTable->getGroups();
        $allRelevantCatIds = [];
        foreach ($groups as $groupName => $groupCatIds) {
            $allRelevantCatIds = array_merge($allRelevantCatIds, $groupCatIds);
        }

        $area = $this->find('all')
            ->select(['Areas.id'])
            ->where(['Areas.id' => $areaId])
            ->contain([
                'Statistics' => function ($q) use ($allRelevantCatIds) {
                    return $q
                        ->where(function ($exp, $q) use ($allRelevantCatIds) {
                            return $exp->in('Statistics.stat_category_id', $allRelevantCatIds);
                        })
                        ->contain(['StatCategories']);
                },
            ])
            ->first();
        if (empty($area) || empty($area->statistics)) {
            return false;
        }

        $table = [];
        foreach ($area['statistics'] as $stat) {
            $categoryId = $stat['stat_category_id'];
            foreach ($groups as $groupName => $catIds) {
                if (in_array($categoryId, $catIds)) {
                    $group = ucwords($groupName);
                    break;
                }
            }
            $category = $stat['stat_category']['name'];
            $table[$group][$category] = $stat['value'];
        }

        return $table;
    }

    /**
     * Returns a GoogleCharts object for an employment line chart for an area, or false if data or area is unavailable
     *
     * @param int|null $areaId Area ID
     * @return \GoogleCharts|bool
     */
    public function getEmploymentLineChart($areaId)
    {
        if (! $areaId) {
            return false;
        }

        $chart = $this->getGoogleChartsObject();
        $chart->type('LineChart');
        $chart->columns([
            'year' => [
                'type' => 'number',
                'label' => 'Year',
            ],
            'exportable' => [
                'type' => 'number',
                'label' => 'Exportable',
            ],
            'non_exportable' => [
                'type' => 'number',
                'label' => 'Non-Exportable',
            ],
            'annotation' => [
                'type' => 'string',
                'role' => 'annotation',
                'label' => 'Annotation',
            ],
        ]);

        $area = $this->find('all')
            ->select(['Areas.id'])
            ->where(['Areas.id' => $areaId])
            ->contain([
                'Statistics' => function ($q) {
                    return $q
                        ->where(function ($exp, $q) {
                            return $exp->in('Statistics.stat_category_id', [18, 19]);
                        })
                        ->order(['Statistics.year' => 'ASC']);
                },
            ])
            ->first();
        if (empty($area) || empty($area->statistics)) {
            return false;
        }

        // Collect data in an easier array to loop through
        $statistics = [];
        foreach ($area['statistics'] as $i => $stat) {
            $year = $stat['year'];
            $value = $stat['value'];
            $categoryId = $stat['stat_category_id'];
            $categoryKey = $categoryId == 18 ? 'exportable' : 'non_exportable';
            $statistics[$year][$categoryKey] = $value;
        }

        // Add rows
        $recessionYears = [1977, 2006];
        foreach ($statistics as $year => $statSet) {
            $row = ['year' => $year];
            foreach ($statSet as $key => $value) {
                $row[$key] = $value;
            }
            $row['recessions'] = 0;
            $row['annotation'] = in_array($year, $recessionYears) ? 'Recession Year' : null;
            $chart->addRow($row);
        }

        // Get a date range that begins/ends with divisible-by-five years
        $years = array_keys($statistics);
        $minYear = min($years);
        $minYear = 5 * (floor($minYear / 5));
        $maxYear = max($years);
        $maxYear = 5 * (ceil($maxYear / 5));

        $chart->options([
            'chartArea' => [
                'width' => '600',
                'height' => '200',
            ],
            'hAxis' => [
                'format' => '####',
                'gridlines' => ['color' => 'transparent'],
                'slantedText' => false,
                'ticks' => range($minYear, $maxYear, 5),
            ],
            'legend' => 'bottom',
            'series' => [
                ['color' => '#ce845f'],
                ['color' => '#8baebc'],
                ['color' => '#e8f0f0'],
            ],
            'seriesType' => 'line',
            'titlePosition' => 'none',
            'width' => 725,
        ]);

        return $chart;
    }

    /**
     * Returns an array used to make an employment growth table for an area, or false if data or area is unavailable
     *
     * @param int|null $areaId Area ID
     * @return array|bool
     */
    public function getEmploymentGrowthTableData($areaId)
    {
        if (! $areaId) {
            return false;
        }

        // Get the most recent year
        $result = $this->find('all')
            ->select(['Areas.id'])
            ->where(['Areas.id' => $areaId])
            ->contain([
                'Statistics' => function ($q) {
                    return $q
                        ->select(['area_id', 'year'])
                        ->where(function ($exp, $q) {
                            return $exp->in('Statistics.stat_category_id', [18, 19]);
                        })
                        ->order(['Statistics.year' => 'DESC'])
                        ->limit(1);
                },
            ])
            ->first();
        if (empty($result) || empty($result->statistics)) {
            return false;
        }

        $laterYear = $result['statistics'][0]['year'];
        $earlierYear = $laterYear - 5;

        // Collect data for table
        $area = $this->find('all')
            ->select(['Areas.id'])
            ->where(['Areas.id' => $areaId])
            ->contain([
                'Statistics' => function ($q) use ($laterYear, $earlierYear) {
                    return $q->where(function ($exp, $q) use ($laterYear, $earlierYear) {
                        return $exp
                            ->in('Statistics.stat_category_id', [18, 19])
                            ->in('Statistics.year', [$laterYear, $earlierYear]);
                    });
                },
            ])
            ->first();
        if (empty($area) || empty($area->statistics)) {
            throw new NotFoundException('No data is currently available for the selected community.');
        }

        $statistics = [];
        foreach ($area['statistics'] as $i => $stat) {
            $year = $stat['year'];
            $value = $stat['value'];
            $categoryId = $stat['stat_category_id'];
            $label = $categoryId == 18 ? 'Exportable' : 'Non-exportable';
            $statistics[$label][$year] = $value;
        }

        $table = [
            'earlier_year' => $earlierYear,
            'later_year' => $laterYear,
            'rows' => [],
        ];
        foreach ($statistics as $label => $years) {
            $row = ['label' => $label];
            foreach ($years as $year => $value) {
                $row[$year] = $value;
            }
            $laterValue = $row[$laterYear];
            $earlierValue = $row[$earlierYear];
            $difference = $laterValue - $earlierValue;
            if ($difference == 0) {
                $row['change'] = 'No change';
            } else {
                $percentDifference = round($difference / $earlierValue * 100);
                $row['change'] = "$percentDifference% ";
                if ($percentDifference > 0) {
                    $row['change'] .= '<img src="/img/chart-up-color.png" />';
                } elseif ($percentDifference < 0) {
                    $row['change'] .= '<img src="/img/chart-down-color.png" />';
                }
            }
            $table['rows'][] = $row;
        }

        return $table;
    }

    /**
     * Returns a GoogleCharts object
     *
     * @return \GoogleCharts
     */
    public function getGoogleChartsObject()
    {
        require_once ROOT . DS . 'plugins' . DS . 'GoogleCharts' . DS . 'vendor' . DS . 'GoogleCharts.php';

        return new \GoogleCharts();
    }

    /**
     * Get the array of PWRRR rankings associated with an area, or FALSE if no valid area was provided
     *
     * @param int|null $areaId Area ID
     * @return array|bool
     */
    public function getPwrrrRanks($areaId)
    {
        if (! $areaId) {
            return false;
        }

        try {
            $area = $this->get($areaId);
        } catch (RecordNotFoundException $e) {
            return false;
        }

        $surveysTable = TableRegistry::get('Surveys');
        $sectors = $surveysTable->getSectors();
        $ranks = [];
        foreach ($sectors as $sector) {
            $ranks[$sector] = $area["{$sector}_rank"];
        }

        return $ranks;
    }

    /**
     * Takes a comma-delimited dump of area data and inserts it into the database
     *
     * Format for each line of $data:
     *     fips,City Name city,County Name,countyFips,P,W,R,R,R
     *
     * @return void
     */
    public function importAreaData()
    {
        $data = '...';
        $data = trim($data);
        $lines = explode("\n", $data);
        foreach ($lines as $line) {
            $line = trim($line);
            $fields = explode(',', $line);
            $fips = $fields[0];
            $parentFips = $fields[3];
            $areaNameWords = explode(' ', $fields[1]);
            $type = array_pop($areaNameWords);
            $areaName = implode(' ', $areaNameWords);
            if (! in_array($type, ['city', 'town', 'CDP'])) {
                exit('Unrecognized area type: ' . $type);
            }

            $recordExists = $this->getIdFromFips($fips);
            if ($recordExists !== null) {
                continue;
            }

            $data = [
                'fips' => $fips,
                'name' => $areaName,
                'type' => $type,
                'production_rank' => $fields[4],
                'wholesale_rank' => $fields[5],
                'retail_rank' => $fields[6],
                'residential_rank' => $fields[7],
                'recreation_rank' => $fields[8],
                'parent_id' => $this->getIdFromFips($parentFips),
            ];
            $area = $this->newEntity($data);
            $errors = $area->getErrors();
            if (empty($errors)) {
                $this->save($area);
                echo 'Saved ' . $areaName . '<br />';
            } else {
                exit('Errors: ' . print_r($errors, true));
            }
        }
    }

    /**
     * Returns the ID of an area with the given FIPS code
     *
     * @param int $fips FIPS code
     * @return int|null
     */
    public function getIdFromFips($fips)
    {
        $result = $this->find('all')
            ->select(['Areas.id'])
            ->where(['Areas.fips' => $fips])
            ->first();

        return $result ? $result->id : null;
    }

    /**
     * Returns an array of ['Capitalized-type' => [$areaId => $areaName], ...]
     *
     * @return array
     */
    public function getGroupedList()
    {
        $result = $this->find('all')
            ->select(['id', 'name', 'type'])
            ->order(['name' => 'ASC'])
            ->toArray();
        $grouped = Hash::combine($result, '{n}.id', '{n}.name', '{n}.type');

        // Unfortunately, this (apparently) can't be accomplished with text-transform: capitalize
        // because area types are displayed in <optgroup label="areatype">
        $capitalizedGrouped = [];
        foreach ($grouped as $type => $areas) {
            $type = ucwords($type);
            $capitalizedGrouped[$type] = $areas;
        }

        return $capitalizedGrouped;
    }
}
