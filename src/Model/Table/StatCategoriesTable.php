<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * StatCategories Model
 *
 * @property \Cake\ORM\Association\HasMany $Statistic
 * @method \App\Model\Entity\StatCategory get($primaryKey, $options = [])
 * @method \App\Model\Entity\StatCategory newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\StatCategory[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\StatCategory|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\StatCategory patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\StatCategory[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\StatCategory findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
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
        $this->setTable('stat_categories');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
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
            $errors = $category->getErrors();
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
