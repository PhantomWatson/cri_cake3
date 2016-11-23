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
        $categoryNames = [];
        $data = '';
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

    /**
     * Processes the string $data to import statistics into the database
     *
     * Used for data grouped thusly:
     *
     *  FIPS, community name, 1st category name, 1969 value, 1970 value, 1971 value...
     *                        2nd category name, 1969 value, 1970 value, 1971 value...
     *
     *  FIPS, community name, 1st category name...
     *  ...
     *
     * @return void
     */
    public function importGrouped()
    {
        // Set to TRUE to prevent changing any database values
        $dryRun = true;

        $data = '';
        $data = trim($data);
        $lines = explode("\n", $data);
        $areasTable = TableRegistry::get('Areas');
        $statCategoriesTable = TableRegistry::get('StatCategories');
        $fips = null;
        $areaName = null;
        $currentAreaId = null;
        $earliestYear = 1969;
        $years = [];
        foreach ($lines as $lineNum => $line) {
            echo "[LINE #$lineNum]<br />";
            $line = trim($line);
            $fields = explode(',', $line);

            // Blank line
            if ($fields[3] == '') {
                continue;
            }

            // Years are being defined
            if ($fields[0] == '' && $fields[1] == '' && $fields[2] == '' && $fields[3] == $earliestYear) {
                $years = array_slice($fields, 3);
                continue;
            }

            // Area is being defined
            if (is_numeric($fields[0])) {
                $fips = $fields[0];
                $areaName = $fields[1];
                $areaId = $areasTable->getIdFromFips($fips);
                if (!$areaId) {
                    exit("Area '$areaName' with FIPS code '$fips' not recognized.");
                }
            }

            // Determine category ID
            $categoryName = $fields[2] . ' Sector Employment';
            $categoryId = $statCategoriesTable->getIdFromName($categoryName);
            if (! $categoryId) {
                exit("Unrecognized category: $categoryName");
            }

            // Cycle through all data fields
            $yearsCount = count($years);
            for ($i = 0; $i < $yearsCount; $i++) {
                $value = $fields[$i + 3];

                // Skip blank values
                if ($value === '') {
                    echo 'Skipping blank<br />';
                    continue;
                }

                // Check for existing record
                $year = $years[$i];
                $existingRecord = $this->find('all')
                    ->select(['id', 'value'])
                    ->where([
                        'area_id' => $areaId,
                        'stat_category_id' => $categoryId,
                        'year' => $year
                    ])
                    ->first();

                if ($existingRecord) {
                    // Skip over any stats that have already been recorded (with an identical value)
                    if ($existingRecord->value == $value) {
                        $msg = 'Skipping already-recorded stat for ' . $year . ': ';
                        $msg .= print_r(['area_id' => $areaId, 'stat_category_id' => $categoryId], true);
                        $msg .= '<br />';
                        echo $msg;
                        continue;
                    }

                    // Update record
                    $oldValue = $existingRecord->value;
                    if ($dryRun) {
                        echo 'Would have updated #' . $existingRecord->id . ": $oldValue -> $value for $year<br />";
                    } else {
                        $existingRecord = $this->patchEntity($existingRecord, ['value' => $value]);
                        $errors = $existingRecord->errors();
                        if (!empty($errors)) {
                            exit('Errors: ' . print_r($errors, true));
                        }
                        $this->save($existingRecord);
                        echo 'Updated #' . $existingRecord->id . ": $oldValue -> $value for $year<br />";
                    }
                    continue;
                }

                // Save new record
                $data = [
                    'area_id' => $areaId,
                    'stat_category_id' => $categoryId,
                    'value' => $value,
                    'year' => $year
                ];
                $statistic = $this->newEntity($data);
                $errors = $statistic->errors();
                if (!empty($errors)) {
                    exit('Errors: ' . print_r($errors, true));
                }
                if ($dryRun) {
                    $this->save($statistic);
                    echo 'Would have saved ' . print_r($data, true) . '<br />';
                } else {
                    $this->save($statistic);
                    echo 'Saved ' . print_r($data, true) . '<br />';
                }
            }
        }
    }
}
