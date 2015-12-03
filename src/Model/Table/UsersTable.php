<?php
namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\Mailer\Email;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \Cake\ORM\Association\HasMany $Purchases
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
        $this->table('users');
        $this->displayField('name');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->hasMany('Purchases', [
            'foreignKey' => 'user_id'
        ]);
        $this->belongsToMany('ConsultantCommunities', [
            'className' => 'Communities',
            'joinTable' => 'communities_consultants',
            'foreignKey' => 'consultant_id',
            'targetForeignKey' => 'community_id',
            'saveStrategy' => 'replace'
        ]);
        $this->belongsToMany('ClientCommunities', [
            'className' => 'Communities',
            'joinTable' => 'clients_communities',
            'foreignKey' => 'client_id',
            'targetForeignKey' => 'community_id',
            'saveStrategy' => 'replace'
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
                }
            ]);

        $validator
            ->requirePresence('name', 'create')
            ->add('name', 'notBlank', [
                'rule' => 'notBlank',
                'message' => 'A non-blank name is required.'
            ]);

        $validator
            ->add('email', 'valid', [
                'rule' => 'email',
                'message' => 'That doesn\'t appear to be a valid email address.'
            ])
            ->add('email', 'unique', [
                'rule' => 'validateUnique',
                'provider' => 'table',
                'message' => 'Sorry, another account has already been created with that email address.'
            ])
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        $validator
            ->requirePresence('password', 'create')
            ->add('password', 'notBlank', [
                'rule' => 'notBlank',
                'message' => 'A non-blank password is required.'
            ]);

        $validator
            ->notEmpty('new_password', 'A password is required', 'create')
            ->allowEmpty('new_password', 'update')
            ->add('new_password', 'validNewPassword1', [
                'rule' => ['compareWith', 'confirm_password'],
                'message' => 'Sorry, those passwords did not match.'
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
     * @param int|null $userId
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
        $communitiesTable = TableRegistry::get('Communities');
        if ($role == 'admin') {
            return $communitiesTable->find('list')->order(['name' => 'ASC']);
        }
        if ($role == 'consultant') {
            return $communitiesTable->getConsultantCommunityList($userId);
        }
        if ($role == 'client') {
            return $communitiesTable->getClientCommunityList($userId);
        }
        return $communitiesTable->find('list')
            ->where(['public' => true])
            ->order(['name' => 'ASC']);
    }

    /**
     * Returns TRUE if the specified user is allowed to view the specified community
     *
     * @param int $userId
     * @param int $communityId
     * @return boolean
     */
    public function canAccessCommunity($userId, $communityId)
    {
        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->get($communityId);

        if ($community->public) {
            return true;
        }

        $user = $this->get($userId);
        if ($user->all_communities || $user->role == 'admin') {
            return true;
        }

        return $this->CommunitiesUser->exists([
            'user_id' => $userId,
            'community_id' => $communityId
        ]);
    }

    public function getClientList()
    {
        return $this->find('list')
            ->where(['role' => 'client'])
            ->order(['name' => 'ASC'])
            ->toArray();
    }

    public function getConsultantList()
    {
        return $this->find('list')
            ->where(['role' => 'consultant'])
            ->order(['name' => 'ASC'])
            ->toArray();
    }

    /**
     * Returns a random six-character string. Ambiguous-looking alphanumeric characters are excluded.
     * @return string
     */
    public function generatePassword()
    {
        $characters = str_shuffle('abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789');
        return substr($characters, 0, 6);
    }

    public function sendNewAccountEmail($user, $password)
    {
        $homeUrl = Router::url('/', true);
        $loginUrl = Router::url([
            'prefix' => false,
            'controller' => 'Users',
            'action' => 'login'
        ], true);
        $email = new Email('new_account');
        $email->to($user->email);
        $email->viewVars(compact(
            'user',
            'homeUrl',
            'loginUrl',
            'password'
        ));
        return $email->send();
    }

    /**
     * Returns TRUE if the user is a client for the specified community.
     *
     * A clearer name might be "user is this community's client".
     *
     * @param int $communityId
     * @param int $userId
     * @return boolean
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
     * @param string $email
     * @return int|null
     */
    public function getIdWithEmail($email)
    {
        $user = $this->find('all')
            ->select(['id'])
            ->where(['email' => $email])
            ->first();
        return $user->id;
    }
}
