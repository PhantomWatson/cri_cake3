<?php
namespace App\Model\Table;

use App\Model\Entity\StatCategory;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * StatCategories Model
 *
 * @property \Cake\ORM\Association\HasMany $Statistic
 */
class StatCategoriesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('stat_categories');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->hasMany('Statistic', [
            'foreignKey' => 'stat_category_id'
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

        return $validator;
    }

    public function import()
    {
        $newCategories = [
            'Production Demand Pull Factor (exportable demand per capita)',
            'Production Supply Pull Factor (exportable supply per capita)',
            'Production Workers per Capita Pull Factor',
            'Production Median Earnings Pull Factor',
            'Wholesale Workers per Capita Pull Factor',
            'Wholesale Median Earnings Pull Factor',
            'Transportation Occupations Median Earnings Pull Factor',
            'Retail Supply Pull Factor (non-exportable supply per capita)',
            'Retail Demand Pull Factor (non-exportable demand per capita)',
            'Retail Trade Workers per Capita Pull Factor',
            'Retail Trade Median Earnings Pull Factor',
            'Housing Density (units per square mile) Pull Factor',
            'Metro Dummy Pull Factor',
            'Median House Value Pull Factor',
            '2000-2013  Population Growth Pull Factor',
            'Index of Changeable Amenities Pull Factor',
            'Index of Relatively Static Amenities Pull Factor',
            'Percentage of 25 Yr & Older Population that have a Bachelor Degree or Higher Pull Factor',
            'Arts, Entertainment, and Recreation Workers per Capita Pull Factor',
            'Arts, Entertainment, and Recreation Median Earnings Pull Factor',
            'Percentage of Total Population under 30 years Pull Factor'
        ];
        foreach ($newCategories as $name) {
            $recordExists = $this->find('all')
                ->where(['name' => $name])
                ->count();
            if ($recordExists !== 0) {
                continue;
            }
            $category = $this->newEntity(['name' => $name]);
            $errors = $category->errors();
            if (empty($errors)) {
                $this->save($category);
                echo 'Saved '.$name.'<br />';
            } else {
                exit('Errors: '.print_r($errors, true));
            }
        }
    }

    /**
     * @param string $name
     * @return int|null
     */
    public function getIdFromName($name)
    {
        $statCategory = $this->find('all')
            ->select(['id'])
            ->where(['name' => $name])
            ->first();
        return $statCategory ? $statCategory->id : null;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return [
            'production' => [1, 2, 20, 21],
            'wholesale' => [3, 4, 5, 22, 23, 24],
            'retail' => [6, 7, 8, 25, 26],
            'residential' => [9, 10, 11, 12, 27],
            'recreation' => [13, 14, 15, 16, 17, 29, 30, 31]
        ];
    }
}
