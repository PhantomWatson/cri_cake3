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
     * @return \Cake\Mailer\Mailer
     */
    public function newAccount($user, $password)
    {
        $email = $this
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
            ->setDomain('cri.cberdata.org');
        $email->viewBuilder()->setTemplate('new_account');

        return $email;
    }

    /**
     * Defines an email with a link that can be used in the next
     * 24 hours to give the user access to /users/resetPassword
     *
     * @param int $userId User ID
     * @return \Cake\Mailer\Mailer
     */
    public function resetPassword($userId)
    {
        $timestamp = time();
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $hash = $usersTable->getPasswordResetHash($userId, $timestamp);
        $user = $usersTable->get($userId);

        $email = $this
            ->setTo($user->email)
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
        $email->viewBuilder()->setTemplate('reset_password');

        return $email;
    }
}
