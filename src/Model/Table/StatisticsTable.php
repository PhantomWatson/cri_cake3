<?php
namespace App\Model\Table;

use App\Model\Entity\Statistic;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Statistics Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Areas
 * @property \Cake\ORM\Association\BelongsTo $StatCategories
 */
class StatisticsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('statistics');
        $this->displayField('value');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Areas', [
            'foreignKey' => 'area_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('StatCategories', [
            'foreignKey' => 'stat_category_id',
            'joinType' => 'INNER'
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
            ->add('value', 'valid', ['rule' => 'numeric'])
            ->requirePresence('value', 'create')
            ->notEmpty('value');

        $validator
            ->add('year', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('year');

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
        $rules->add($rules->existsIn(['stat_category_id'], 'StatCategories'));
        return $rules;
    }

    /**
     * Processes the string $data to import statistics into the database
     *
     * @return void
     */
    public function import()
    {
        // Stat category names, in the order they're arranged in the spreadsheet to be imported
        // Assumed to have already been added to the stat_categories table
        $categoryNames = ['2001-2011 Population Growth Pull Factor'];
        $data = '
            18000,Indiana,1.123,
            18001,Adams,1.035,
            18003,Allen,1.137,
            18005,Bartholomew,1.160,
            18007,Benton,0.869,
            18009,Blackford,0.793,
            18011,Boone,1.480,
            18013,Brown,0.965,
            18015,Carroll,0.951,
            18017,Cass,0.868,
            18019,Clark,1.313,
            18021,Clay,1.005,
            18023,Clinton,0.933,
            18025,Crawford,0.926,
            18027,Daviess,1.149,
            18029,Dearborn,1.144,
            18031,Decatur,1.105,
            18033,DeKalb,1.085,
            18035,Delaware,0.931,
            18037,Dubois,1.096,
            18039,Elkhart,1.154,
            18041,Fayette,0.893,
            18043,Floyd,1.092,
            18045,Fountain,0.912,
            18047,Franklin,1.054,
            18049,Fulton,1.020,
            18051,Gibson,1.040,
            18053,Grant,0.884,
            18055,Greene,0.960,
            18057,Hamilton,2.000,
            18059,Hancock,1.528,
            18061,Harrison,1.269,
            18063,Hendricks,1.754,
            18065,Henry,1.021,
            18067,Howard,0.924,
            18069,Huntington,0.941,
            18071,Jackson,1.069,
            18073,Jasper,1.202,
            18075,Jay,0.937,
            18077,Jefferson,1.006,
            18079,Jennings,1.002,
            18081,Johnson,1.435,
            18083,Knox,0.961,
            18085,Kosciusko,1.058,
            18087,Lagrange,1.113,
            18089,Lake,1.032,
            18091,LaPorte,1.007,
            18093,Lawrence,0.992,
            18095,Madison,0.952,
            18097,Marion,1.099,
            18099,Marshall,1.055,
            18101,Martin,0.985,
            18103,Miami,0.989,
            18105,Monroe,1.315,
            18107,Montgomery,1.019,
            18109,Morgan,1.067,
            18111,Newton,0.949,
            18113,Noble,1.002,
            18115,Ohio,1.153,
            18117,Orange,1.050,
            18119,Owen,0.934,
            18121,Parke,0.985,
            18123,Perry,1.035,
            18125,Pike,0.949,
            18127,Porter,1.241,
            18129,Posey,0.879,
            18131,Pulaski,0.937,
            18133,Putnam,1.075,
            18135,Randolph,0.892,
            18137,Ripley,1.116,
            18139,Rush,0.874,
            18141,St. Joseph,0.991,
            18143,Scott,1.044,
            18145,Shelby,1.011,
            18147,Spencer,1.035,
            18149,Starke,0.979,
            18151,Steuben,1.012,
            18153,Sullivan,0.937,
            18155,Switzerland,1.243,
            18157,Tippecanoe,1.332,
            18159,Tipton,0.882,
            18161,Union,1.039,
            18163,Vanderburgh,1.083,
            18165,Vermillion,0.925,
            18167,Vigo,1.038,
            18169,Wabash,0.845,
            18171,Warren,0.948,
            18173,Warrick,1.300,
            18175,Washington,1.035,
            18177,Wayne,0.914,
            18179,Wells,0.989,
            18181,White,0.922,
            18183,Whitley,1.154,
        ';
        $data = trim($data);
        $lines = explode("\n", $data);
        $areasTable = TableRegistry::get('Areas');
        $statCategoriesTable = TableRegistry::get('StatCategories');
        $categoryCount = count($categoryNames);
        $areaIds = [];
        $categoryIds = [];
        foreach ($lines as $lineNum => $line) {
            echo "[LINE #$lineNum]<br />";
            $line = trim($line);
            $fields = explode(',', $line);
            $fips = $fields[0];
            $areaName = $fields[1];

            if (isset($areaIds[$fips])) {
                $areaId = $areaIds[$fips];
            } else {
                $areaId = $areasTable->getIdFromFips($fips);
                if (! $areaId) {
                    exit("Area '$areaName' with FIPS code '$fips' not recognized.");
                }
                $areaIds[$fips] = $areaId;
            }

            // Cycle through all data fields
            for ($i = 0; $i < $categoryCount; $i++) {
                $value = $fields[$i + 2];

                // Skip blank values
                if ($value === '') {
                    echo 'Skipping blank<br />';
                    continue;
                }

                $categoryName = $categoryNames[$i];
                if (isset($categoryIds[$categoryName])) {
                    $categoryId = $categoryIds[$categoryName];
                } else {
                    $categoryId = $statCategoriesTable->getIdFromName($categoryName);
                    if (! $categoryId) {
                        exit("Unrecognized category: $categoryName");
                    }
                    $categoryIds[$categoryName] = $categoryId;
                }

                $existingRecord = $this->find('all')
                    ->select(['id', 'value'])
                    ->where([
                        'area_id' => $areaId,
                        'stat_category_id' => $categoryId
                    ])
                    ->first();
                if ($existingRecord) {
                    // Skip over any stats that have already been recorded (but overwrite with new data)
                    if ($existingRecord->value == $value) {
                        $msg = 'Skipping already-recorded stat: ';
                        $msg .= print_r(['area_id' => $areaId, 'stat_category_id' => $categoryId], true);
                        $msg .= '<br />';
                        echo $msg;
                        continue;
                    }

                    // Update record
                    $oldValue = $existingRecord->value;
                    $existingRecord = $this->patchEntity($existingRecord, ['value' => $value]);
                    $errors = $existingRecord->errors();
                    if (! empty($errors)) {
                        exit('Errors: ' . print_r($errors, true));
                    }
                    $this->save($existingRecord);
                    echo 'Updated #' . $existingRecord->id . ": $oldValue -> $value<br />";

                // Save new record
                } else {
                    $data = [
                        'area_id' => $areaId,
                        'stat_category_id' => $categoryId,
                        'value' => $value
                    ];
                    $statistic = $this->newEntity($data);
                    $errors = $statistic->errors();
                    if (! empty($errors)) {
                        exit('Errors: ' . print_r($errors, true));
                    }
                    $this->save($statistic);
                    echo 'Saved ' . print_r($data, true) . '<br />';
                }
            }
        }
    }
}
