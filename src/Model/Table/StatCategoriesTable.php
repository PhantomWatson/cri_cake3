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

    /**
     * Imports new categories (when they're specified in $newCategories)
     *
     * A hacky way to add a bunch of records safely while avoiding redundancy
     *
     * @return void
     */
    public function import()
    {
        $newCategories = [];
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
                echo "Saved $name<br />";
            } else {
                exit('Errors: ' . print_r($errors, true));
            }
        }
    }

    /**
     * Returns a stat category ID for the given stat category name
     *
     * @param string $name Stat category name
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
     * Returns an array of sets of statistic category IDs grouped by PWRRR labels
     *
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
