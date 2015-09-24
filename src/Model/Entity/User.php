<?php
namespace App\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * User Entity.
 */
class User extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'role' => true,
        'name' => true,
        'email' => true,
        'phone' => true,
        'title' => true,
        'organization' => true,
        'password' => true,
        'all_communities' => true,
        'purchases' => true,
        'client_communities' => true

    ];

    protected function _setPassword($password)
    {
        return (new DefaultPasswordHasher)->hash($password);
    }
}
