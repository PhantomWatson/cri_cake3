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
        $area = $this->find('first', [
            'conditions' => ['Area.id' => $areaId],
            'fields' => ['Area.id'],
            'contain' => [
                'Statistic' => [
                    'conditions' => ['Statistic.stat_category_id' => range(1, 17)],
                    'StatCategory'
                ]
            ]
        ]);

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
}
