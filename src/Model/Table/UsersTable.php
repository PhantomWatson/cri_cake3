<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \App\Model\Table\PurchasesTable&\Cake\ORM\Association\HasMany $Purchases
 * @property \App\Model\Table\OptOutsTable&\Cake\ORM\Association\HasMany $OptOuts
 * @property \App\Model\Table\CommunitiesTable&\Cake\ORM\Association\BelongsToMany $ConsultantCommunities
 * @property \App\Model\Table\CommunitiesTable&\Cake\ORM\Association\BelongsToMany $ClientCommunities
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @property \Cake\ORM\Table&\Cake\ORM\Association\HasMany $CommunitiesUsers
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false saveMany($entities, $options = [])
 */
class UsersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->setTable('users');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->hasMany('Purchases', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('OptOuts', [
            'foreignKey' => 'user_id',
        ]);
        $this->belongsToMany('ConsultantCommunities', [
            'className' => 'Communities',
            'joinTable' => 'communities_consultants',
            'foreignKey' => 'consultant_id',
            'targetForeignKey' => 'community_id',
            'saveStrategy' => 'replace',
        ]);
        $this->belongsToMany('ClientCommunities', [
            'className' => 'Communities',
            'joinTable' => 'clients_communities',
            'foreignKey' => 'client_id',
            'targetForeignKey' => 'community_id',
            'saveStrategy' => 'replace',
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
            ->requirePresence('role', 'create')
            ->notEmpty('role')
            ->add('role', 'valid', [
                'rule' => function ($data, $provider) {
                    if (in_array($data, ['admin', 'client', 'consultant'])) {
                        return true;
                    }

                    return 'Role must be admin, client, or consultant.';
                },
            ]);

        $validator
            ->requirePresence('name', 'create')
            ->add('name', 'notBlank', [
                'rule' => 'notBlank',
                'message' => 'A non-blank name is required.',
            ]);

        $validator
            ->add('email', 'valid', [
                'rule' => 'email',
                'message' => 'That doesn\'t appear to be a valid email address.',
            ])
            ->add('email', 'unique', [
                'rule' => 'validateUnique',
                'provider' => 'table',
                'message' => 'Sorry, another account has already been created with that email address.',
            ])
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        $validator
            ->requirePresence('password', 'create')
            ->add('password', 'notBlank', [
                'rule' => 'notBlank',
                'message' => 'A non-blank password is required.',
            ]);

        $validator
            ->add('unhashed_password', 'notBlank', [
                'rule' => 'notBlank',
                'message' => 'A non-blank password is required.',
            ]);

        $validator
            ->notEmpty('new_password', 'A password is required', 'create')
            ->allowEmpty('new_password', 'update')
            ->add('new_password', 'validNewPassword1', [
                'rule' => ['compareWith', 'confirm_password'],
                'message' => 'Sorry, those passwords did not match.',
            ]);

        $validator
            ->notEmpty('confirm_password', 'A password is required', 'create')
            ->allowEmpty('confirm_password', 'update');

        $validator
            ->add('all_communities', 'valid', ['rule' => 'boolean']);

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
        return $rules;
    }

    /**
     * Returns the communities that this user has access to.
     *
     * @param int|null $userId User ID
     * @return array of $communityId => $community_name pairs
     */
    public function getAccessibleCommunities($userId = null)
    {
        if (! $userId) {
            $role = null;
        } else {
            try {
                $user = $this->get($userId);
                $role = $user->role;
            } catch (RecordNotFoundException $e) {
                $role = null;
            }
        }
        $communitiesTable = TableRegistry::getTableLocator()->get('Communities');
        $query = $communitiesTable->find()
            ->select(['id', 'name', 'slug'])
            ->order(['name' => 'ASC']);
        if ($role == 'admin') {
            return $query->toArray();
        }

        return $query
            ->where(['public' => true])
            ->toArray();
    }

    /**
     * Returns TRUE if the specified user is allowed to view the specified community
     *
     * @param int $userId User ID
     * @param \App\Model\Entity\Community $community Community entity
     * @return bool
     */
    public function canAccessCommunity($userId, $community)
    {
        // Everyone can view communities marked 'public'
        if ($community->public) {
            return true;
        }

        // Non-public communities can't be accessed by anonymous users
        if (! $userId) {
            return false;
        }

        // Users granted the 'admin' role or all-communities access can access all communities
        $user = $this->get($userId);
        if ($user->all_communities || $user->role == 'admin') {
            return true;
        }

        // Otherwise, access is not granted
        return false;
    }

    /**
     * Returns an array of client full-names, indexed by client user IDs
     *
     * @return array
     */
    public function getClientList()
    {
        $clients = $this->find('all')
            ->select(['id', 'salutation', 'name'])
            ->where(['role' => 'client'])
            ->order(['name' => 'ASC']);
        $retval = [];
        foreach ($clients as $client) {
            $retval[$client->id] = $client->full_name;
        }

        return $retval;
    }

    /**
     * Returns a random six-character string. Ambiguous-looking alphanumeric characters are excluded.
     *
     * @return string
     */
    public function generatePassword()
    {
        $characters = str_shuffle('abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789');

        return substr($characters, 0, 6);
    }

    /**
     * Returns TRUE if the user is a client for the specified community.
     *
     * A clearer name might be "user is this community's client".
     *
     * @param int $communityId Community ID
     * @param int $userId User ID
     * @return bool
     */
    public function isCommunityClient($communityId, $userId)
    {
        $count = $this->find('all')
            ->where(['Users.id' => $userId])
            ->matching('ClientCommunities', function ($q) use ($communityId) {
                return $q->where(['ClientCommunities.id' => $communityId]);
            })
            ->count();

        return $count > 0;
    }

    /**
     * Returns the user ID that matches the provided email
     *
     * @param string $email Email address
     * @return int|null
     */
    public function getIdWithEmail($email)
    {
        $user = $this->find('all')
            ->select(['id'])
            ->where(['email' => $email])
            ->limit(1);
        if ($user->isEmpty()) {
            return null;
        }

        return $user->first()->id;
    }

    /**
     * Returns a hash for use in the emailed link to /reset-password
     *
     * @param int $userId User ID
     * @param int $timestamp Timestamp
     * @return string
     */
    public function getPasswordResetHash($userId, $timestamp)
    {
        return Security::hash($userId . $timestamp, 'sha1', true);
    }

    /**
     * Return the possible values for the 'salutation' field
     *
     * @return array
     */
    public function getSalutations()
    {
        $salutations = ['', 'Mr.', 'Ms.', 'Dr.', 'Rev.', 'Prof.'];

        return array_combine($salutations, $salutations);
    }

    /**
     * Returns all users who have opted in to receive admin task emails for the specified admin group
     *
     * @param string $group Either 'ICI', 'CBER', or 'both'
     * @return \Cake\Datasource\ResultSetInterface
     */
    public function getAdminEmailRecipients($group)
    {
        if ($group == 'ICI') {
            return $this->find()->where(['ici_email_optin' => true])->all();
        }

        if ($group == 'CBER') {
            return $this->find()->where(['cber_email_optin' => true])->all();
        }

        if ($group == 'both') {
            return $this->find()->where([
                'OR' => [
                    'cber_email_optin' => true,
                    'ici_email_optin' => true,
                ],
            ])->all();
        }

        throw new InternalErrorException('Unrecognized admin group: ' . $group);
    }
}
