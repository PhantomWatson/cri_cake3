<?php
namespace App\Model\Table;

use App\Model\Entity\Area;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Areas Model
 *
 * @property \Cake\ORM\Association\HasMany $Communities
 * @property \Cake\ORM\Association\HasMany $Statistic
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
        $this->table('areas');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->hasMany('Communities', [
            'foreignKey' => 'area_id'
        ]);
        $this->hasMany('Statistic', [
            'foreignKey' => 'area_id'
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
     * @param $areaId int
     * @return GoogleCharts
     */
    public function getPwrBarChart($areaId)
    {
        $area = $this->find('all')
            ->select(['Area.id'])
            ->where(['Area.id' => $areaId])
            ->contain([
                'Statistic' => function ($q) {
                    return $q
                        ->where(['Statistic.stat_category_id' => range(1, 17)])
                        ->contain(['StatCategory']);
                }
            ])
            ->first();

        // Initialize chart
        $chart = new GoogleCharts();
        $chart->type('ComboChart');

        /* To have each group of bars share one color, each group needs to constitute its own series.
         * Each row is added like this:
         *      Production row: #, #, 0, 0, 0, 0, 0, ...
         *      Wholesale row:  0, 0, #, #, #, 0, 0, ...
         *      Retail row:     0, 0, 0, 0, 0, #, #, ... */
        $columns = [
            'category' => [
                'type' => 'string',
                'label' => 'Category'
            ],
            'score_production' => [
                'type' => 'number',
                'label' => 'Production'
            ],
            'score_wholesale' => [
                'type' => 'number',
                'label' => 'Wholesale'
            ],
            'score_retail' => [
                'type' => 'number',
                'label' => 'Retail'
            ],
            'score_residential' => [
                'type' => 'number',
                'label' => 'Residential'
            ],
            'score_recreation' => [
                'type' => 'number',
                'label' => 'Recreation'
            ],
            'average' => [
                'type' => 'number',
                'label' => 'National average'
            ],
            'certainty' => [
                'type' => 'boolean',
                'role' => 'certainty',
                'label' => 'Certainty'
            ],
            'annotation' => [
                'type' => 'string',
                'role' => 'annotation',
                'label' => 'Annotation'
            ]
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

        foreach ($area['Statistic'] as $i => $stat) {
            $categoryId = $stat['stat_category_id'];
            $row = [];
            foreach ($columnGroups as $columnGroup) {
                if (($columnGroup == 'score_production'    && $categoryId <= 2) ||
                    ($columnGroup == 'score_wholesale'     && $categoryId > 2 && $categoryId <= 5) ||
                    ($columnGroup == 'score_retail'        && $categoryId > 5 && $categoryId <= 8) ||
                    ($columnGroup == 'score_residential'   && $categoryId > 8 && $categoryId <= 12) ||
                    ($columnGroup == 'score_recreation'    && $categoryId > 12 && $categoryId <= 17)
                ) {
                    $value = $stat['value'];
                    $allValues[] = $value;
                } else {
                    $value = 0;
                }
                $row[$columnGroup] = $value;
            }
            $row['category'] = $stat['StatCategory']['name'];
            $row['average'] = 1;
            $row['certainty'] = 'false';

            // Place an annotation above the second column
            $row['annotation'] = ($i == 1) ? 'National Average' : '';

            $chart->addRow($row);
        }

        // Add the national average line also to the end of the graph
        $lastRow = $firstRow;
        $lastRow['annotation'] = '';
        $chart->addRow($lastRow);

        // Determine the min and max values of the vertical axis based on the min and max values
        $maxValue = max($allValues);
        $axisMax = ceil(($maxValue * 2)) / 2;
        $minValue = min($allValues);
        $axisMin = floor(($minValue * 2)) / 2;

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
                'groupWidth' => '95%'
            ],
            'chartArea' => [
                'width' => '90%',
                'height' => '80%'
            ],
            'hAxis' => [
                'textPosition' => 'none',
                'viewWindow' => [
                    'min' => 1,
                    'max' => count($area['Statistic']) + 1
                ]
            ],
            'isStacked' => true,
            'legend' => 'bottom',
            'series' => [
                [
                    'color' => '#ce845f',
                    'targetAxisIndex' => 1
                ],
                ['color' => '#d9bc7b'],
                ['color' => '#a8b28a'],
                ['color' => '#8baebc'],
                ['color' => '#a6ccc6'],
                [
                    'color' => '#cc0022',
                    'lineWidth' => 1,
                    'type' => 'line',
                    'visibleInLegend' => false
                ]
            ],
            'seriesType' => 'bars',
            'titlePosition' => 'none',
            'vAxis' => [
                'maxValue' => $axisMax,
                'minValue' => $axisMin,
                'viewWindow' => [
                    'max' => $axisMax + 0.5,
                    'min' => $axisMin - 0.5
                ],
                'ticks' => $ticks,
                'format' => '#.0'
            ],
            'width' => 725
        ]);

        return $chart;
    }

    /**
     * @param $area_id int
     * @return array
     */
    public function getPwrTable($area_id)
    {
        $area = $this->find('all')
            ->select(['Area.id'])
            ->where(['Area.id' => $area_id])
            ->contain([
                'Statistic' => function ($q) {
                    return $q->where(['Statistic.stat_category_id' => range(1, 17)]);
                },
                'StatCategory'
            ])
            ->first();
        $table = [];
        foreach ($area['Statistic'] as $stat) {
            $category_id = $stat['stat_category_id'];
            if ($category_id <= 2) {
                $group = 'Production';
            } elseif ($category_id > 2 && $category_id <= 5) {
                $group = 'Wholesale';
            } elseif ($category_id > 5 && $category_id <= 8) {
                $group = 'Retail';
            } elseif ($category_id > 8 && $category_id <= 12) {
                $group = 'Residential';
            } elseif ($category_id > 12 && $category_id <= 17) {
                $group = 'Recreation';
            }
            $category = $stat['StatCategory']['name'];
            $table[$group][$category] = $stat['value'];
        }
        return $table;
    }

    /**
     * @param $area_id int
     * @return GoogleCharts
     */
    public function getEmploymentLineChart($area_id)
    {
        $chart = new GoogleCharts();
        $chart->type('LineChart');
        $chart->columns([
            'year' => [
                'type' => 'number',
                'label' => 'Year'
            ],
            'exportable' => [
                'type' => 'number',
                'label' => 'Exportable'
            ],
            'non_exportable' => [
                'type' => 'number',
                'label' => 'Non-Exportable'
            ],
            'annotation' => [
                'type' => 'string',
                'role' => 'annotation',
                'label' => 'Annotation'
            ]
        ]);

        $area = $this->find('all')
            ->select(['Area.id'])
            ->where(['Area.id' => $area_id])
            ->contain([
                'Statistic' => function ($q) {
                    return $q
                        ->where(['Statistic.stat_category_id' => [18,19]])
                        ->order(['Statistic.year' => 'ASC']);
                }
            ])
            ->first();

        // Collect data in an easier array to loop through
        $statistics = [];
        foreach ($area['Statistic'] as $i => $stat) {
            $year = $stat['year'];
            $value = $stat['value'];
            $category_id = $stat['stat_category_id'];
            $category_key = ($category_id == 18) ? 'exportable' : 'non_exportable';
            $statistics[$year][$category_key] = $value;
        }

        // Add rows
        $recession_years = [1977, 2006];
        foreach ($statistics as $year => $stat_set) {
            $row = ['year' => $year];
            foreach ($stat_set as $key => $value) {
                $row[$key] = $value;
            }
            $row['recessions'] = 0;
            $row['annotation'] = '';
            $chart->addRow($row);
        }

        // Get a date range that begins/ends with divisible-by-five years
        $years = array_keys($statistics);
        $min_year = min($years);
        $min_year = 5 * (floor($min_year / 5));
        $max_year = max($years);
        $max_year = 5 * (ceil($max_year / 5));

        $chart->options([
            'chartArea' => [
                'width' => '600',
                'height' => '200'
            ],
            'hAxis' => [
                'format' => '####',
                'gridlines' => ['color' => 'transparent'],
                'slantedText' => false,
                'ticks' => range($min_year, $max_year, 5)
            ],
            'legend' => 'bottom',
            'series' => [
                ['color' => '#ce845f'],
                ['color' => '#8baebc'],
                ['color' => '#e8f0f0']
            ],
            'seriesType' => 'line',
            'titlePosition' => 'none',
            'width' => 725
        ]);

        return $chart;
    }
}
