<?php
declare(strict_types=1);

namespace App\Mailer;

use Cake\Mailer\Mailer;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class UserMailer extends Mailer
{
    /**
     * Defines an email informing a user that their account has been created
     *
     * @param array $user User data
     * @param string $password Unhashed password
     * @return Mailer
     */
    public function newAccount($user, $password)
    {
        return $this
            ->setTo($user['email'])
            ->setSubject('Your new Community Readiness Initiative account has been created')
            ->setViewVars([
                'homeUrl' => Router::url('/', true),
                'loginUrl' => Router::url([
                    'plugin' => false,
                    'prefix' => false,
                    'controller' => 'Users',
                    'action' => 'login',
                ], true),
                'password' => $password,
                'role' => $user['role'],
                'name' => $user['name'],
                'email' => $user['email'],
            ])
            ->setTemplate('new_account')
            ->setDomain('cri.cberdata.org');
    }

    /**
     * Defines an email with a link that can be used in the next
     * 24 hours to give the user access to /users/resetPassword
     *
     * @param int $userId User ID
     * @return Mailer
     */
    public function resetPassword($userId)
    {
        $timestamp = time();
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $hash = $usersTable->getPasswordResetHash($userId, $timestamp);
        $user = $usersTable->get($userId);

        return $this
            ->setTo($user->email)
            ->setTemplate('reset_password')
            ->setSubject('CRI Account Password Reset')
            ->setViewVars([
                'user' => $user,
                'resetUrl' => Router::url([
                    'plugin' => false,
                    'prefix' => false,
                    'controller' => 'Users',
                    'action' => 'resetPassword',
                    $userId,
                    $timestamp,
                    $hash,
                ], true),
            ])
            ->setDomain('cri.cberdata.org');
    }
}
