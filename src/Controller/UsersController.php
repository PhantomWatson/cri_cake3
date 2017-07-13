<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Mailer\MailerAwareTrait;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    use MailerAwareTrait;

    /**
     * initialize method
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['login', 'logout', 'forgotPassword', 'resetPassword']);
    }

    /**
     * Method for /users/login
     *
     * @return \Cake\Http\Response|null
     */
    public function login()
    {
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
                if ($this->Auth->authenticationProvider()->needsPasswordRehash()) {
                    $user = $this->Users->get($this->Auth->user('id'));
                    $user = $this->Users->patchEntity($user, ['password' => $this->request->getData('password')]);
                    $this->Users->save($user);
                }

                // Remember login information
                if ($this->request->getData('auto_login')) {
                    $this->Cookie->configKey('CookieAuth', [
                        'expires' => '+1 year',
                        'httpOnly' => true
                    ]);
                    $this->Cookie->write('CookieAuth', [
                        'email' => $this->request->getData('email'),
                        'password' => $this->request->getData('password')
                    ]);
                }

                return $this->redirect($this->Auth->redirectUrl());
            } else {
                $this->Flash->error('Email or password is incorrect');
            }
        } else {
            $user = $this->Users->newEntity();
            $user->auto_login = true;
        }
        $this->set([
            'titleForLayout' => 'Log in',
            'user' => $user
        ]);
    }

    /**
     * Method for /users/logout
     *
     * @return \Cake\Http\Response|null
     */
    public function logout()
    {
        return $this->redirect($this->Auth->logout());
    }

    /**
     * isAuthorized method
     *
     * @param array $user User
     * @return bool
     */
    public function isAuthorized($user)
    {
        if (parent::isAuthorized($user)) {
            return true;
        }

        $accessible = ['changePassword', 'updateContact'];

        return in_array($this->request->action, $accessible);
    }

    /**
     * Method for /users/change-password
     *
     * @return void
     */
    public function changePassword()
    {
        $userId = $this->Auth->user('id');
        $user = $this->Users->get($userId);
        if ($this->request->is('post') || $this->request->is('put')) {
            $data = $this->request->getData();
            $data['password'] = $this->request->getData('new_password');
            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success('Your password has been updated');
            }
        }
        $this->set([
            'titleForLayout' => 'Change Password',
            'user' => $this->Users->newEntity()
        ]);
    }

    /**
     * Method for /users/update-contact
     *
     * @return void
     */
    public function updateContact()
    {
        $id = $this->Auth->user('id');
        $user = $this->Users->get($id);
        if ($this->request->is('post') || $this->request->is('put')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if (! $user->getErrors()) {
                $saveResult = $this->Users->save($user, $this->request->getData(), [
                    'fieldList' => ['name', 'email']
                ]);
                if ($saveResult) {
                    $this->Flash->success('Your account information has been updated');
                }
            }
        }
        $this->set([
            'titleForLayout' => 'Update Account Contact Info',
            'user' => $user
        ]);
    }

    /**
     * Allows the user to enter their email address and get a link to reset their password
     *
     * @return void
     */
    public function forgotPassword()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            $email = strtolower(trim($email));
            $adminEmail = Configure::read('admin_email');
            if (empty($email)) {
                $msg =
                    'Please enter the email address you registered with to have your password reset. ' .
                    "Email <a href=\"mailto:$adminEmail\">$adminEmail</a> for assistance.";
                $this->Flash->error($msg);
            } else {
                $userId = $this->Users->getIdWithEmail($email);
                if ($userId) {
                    try {
                        $this->getMailer('User')->send('resetPassword', [$userId]);
                        $msg = 'Success! You should be shortly receiving an email with a link to reset your password.';
                        $this->Flash->success($msg);
                        $this->set('success', true);
                    } catch (\Exception $e) {
                        $msg =
                            'There was an error sending your password-resetting email. ' .
                            "Please try again, or email <a href=\"mailto:$adminEmail\">$adminEmail</a> for assistance.";
                        $this->Flash->error($msg);
                    }
                } else {
                    $msg =
                        "We couldn't find an account registered with the email address <strong>$email</strong>. " .
                        'Please make sure you spelled it correctly, and email ' .
                        "<a href=\"mailto:$adminEmail\">$adminEmail</a> if you need assistance.";
                    $this->Flash->error($msg);
                }
            }
        }
        $this->set([
            'titleForLayout' => 'Forgot Password',
            'user' => $user
        ]);
    }

    /**
     * Method for /users/reset-password
     *
     * @param null|int $userId User ID
     * @param null|int $timestamp Timestamp of hash generation
     * @param null|string $hash Security hash emailed to user
     * @return \Cake\Http\Response|null
     * @throws NotFoundException
     * @throws ForbiddenException
     */
    public function resetPassword($userId = null, $timestamp = null, $hash = null)
    {
        if (! $userId || ! $timestamp && ! $hash) {
            throw new NotFoundException('Incomplete URL for password-resetting. Did you leave out part of the URL when you copied and pasted it?');
        }

        if (time() - $timestamp > 60 * 60 * 24) {
            throw new ForbiddenException('Sorry, that link has expired.');
        }

        $expectedHash = $this->Users->getPasswordResetHash($userId, $timestamp);
        if ($hash != $expectedHash) {
            throw new ForbiddenException('Invalid security key');
        }

        $user = $this->Users->get($userId);
        $email = $user->email;

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            $data['password'] = $this->request->getData('new_password');
            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success('Your password has been updated.');

                return $this->redirect(['action' => 'login']);
            }
        }

        $this->set([
            'email' => $email,
            'titleForLayout' => 'Reset Password',
            'user' => $this->Users->newEntity()
        ]);
    }
}
