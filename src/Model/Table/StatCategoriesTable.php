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
            ->limit(1);
        return $statCategory ? $statCategory->id : null;
    }
}
