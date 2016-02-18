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
            // Skip lines
            if (false) {
                continue;
            }

            echo '[LINE #'.$lineNum.']<br />';
            $line = trim($line);
            $fields = explode(',', $line);
            $fips = $fields[0];
            $areaName = $fields[1];

            if (isset($areaIds[$fips])) {
                $areaId = $areaIds[$fips];
            } else {
                $areaId = $areasTable->getIdFromFips($fips);
                if (! $areaId) {
                    exit('Area "'.$areaName.'" with FIPS code '.$fips.' not recognized.');
                }
                $areaIds[$fips] = $areaId;
            }

            // Cycle through all data fields
            for ($i = 0; $i < $categoryCount; $i++) {
                $value = $fields[$i + 4];

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
                        exit('Unrecognized category: '.$categoryName);
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
                        echo 'Skipping already-recorded stat: '.print_r(['area_id' => $areaId, 'stat_category_id' => $categoryId], true).'<br />';
                        continue;
                    }

                    // Update record
                    $oldValue = $existingRecord->value;
                    $existingRecord = $this->patchEntity($existingRecord, ['value' => $value]);
                    $errors = $existingRecord->errors();
                    if (! empty($errors)) {
                        exit('Errors: '.print_r($errors, true));
                    }
                    $this->save($existingRecord);
                    echo 'Updated #'.$existingRecord->id.': '.$oldValue.' -> '.$value.'<br />';

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
                        exit('Errors: '.print_r($errors, true));
                    }
                    $this->save($statistic);
                    echo 'Saved '.print_r($data, true).'<br />';
                }
            }
        }
    }
}
