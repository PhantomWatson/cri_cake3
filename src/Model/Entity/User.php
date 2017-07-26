<?php
namespace App\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * User Entity.
 *
 * @property int $id
 * @property string $role
 * @property string $salutation
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $title
 * @property string $organization
 * @property string $password
 * @property bool $all_communities
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
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
        'salutation' => true,
        'name' => true,
        'email' => true,
        'phone' => true,
        'title' => true,
        'organization' => true,
        'password' => true,
        'all_communities' => true,
        'purchases' => true,
        'client_communities' => true,
        'consultant_communities' => true
    ];

    /**
     * Automatically hashes password
     *
     * @param string $password Password
     * @return bool|string
     */
    protected function _setPassword($password)
    {
        return (new DefaultPasswordHasher)->hash($password);
    }

    /**
     * Gets the user's name, combined with a salutation if provided
     *
     * @return string
     */
    protected function _getFullName()
    {
        if ($this->_properties['salutation'] == '') {
            return $this->_properties['name'];
        }

        return $this->_properties['salutation'] . ' ' . $this->_properties['name'];
    }
}
