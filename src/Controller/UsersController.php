<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Exception\NotFoundException;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['login', 'logout', 'forgotPassword', 'resetPassword']);
    }

    public function login()
    {
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
                if ($this->Auth->authenticationProvider()->needsPasswordRehash()) {
                    $user = $this->Users->get($this->Auth->user('id'));
                    $user = $this->Users->patchEntity($user, ['password' => $this->request->data('password')]);
                    $this->Users->save($user);
                }

                // Remember login information
                if ($this->request->data('auto_login')) {
                    $this->Cookie->configKey('CookieAuth', [
                        'expires' => '+1 year',
                        'httpOnly' => true
                    ]);
                    $this->Cookie->write('CookieAuth', [
                        'email' => $this->request->data('email'),
                        'password' => $this->request->data('password')
                    ]);
                }

                return $this->redirect($this->Auth->redirectUrl());
            } else {
                $this->Flash->error('Email or password is incorrect');
            }
        } else {
            $this->request->data['auto_login'] = true;
        }
        $this->set([
            'titleForLayout' => 'Log in',
            'user' => $this->Users->newEntity()
        ]);
    }

    public function logout()
    {
        return $this->redirect($this->Auth->logout());
    }

    public function isAuthorized($user)
    {
        if (parent::isAuthorized($user)) {
            return true;
        }

        $accessible = ['changePassword', 'updateContact'];
        return in_array($this->request->action, $accessible);
    }

    public function changePassword()
    {
        $userId = $this->Auth->user('id');
        $user = $this->Users->get($userId);
        if ($this->request->is('post') || $this->request->is('put')) {
            $this->request->data['password'] = $this->request->data('new_password');
            $user = $this->Users->patchEntity($user, $this->request->data());
            if ($this->Users->save($user)) {
                $this->Flash->success('Your password has been updated');
            }
        }
        $this->request->data = [];
        $this->set([
            'titleForLayout' => 'Change Password',
            'user' => $user
        ]);
    }

    public function updateContact()
    {
        $id = $this->Auth->user('id');
        $user = $this->Users->get($id);
        if ($this->request->is('post') || $this->request->is('put')) {
            $user = $this->Users->patchEntity($user, $this->request->data());
            if (! $user->errors()) {
                $saveResult = $this->Users->save($user, $this->request->data(), [
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
     */
    public function forgotPassword()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $email = $this->request->data('email');
            $email = strtolower(trim($email));
            $adminEmail = Configure::read('admin_email');
            if (empty($email)) {
                $msg = 'Please enter the email address you registered with to have your password reset. ';
                $msg .= 'Email <a href="mailto:'.$adminEmail.'">'.$adminEmail.'</a> for assistance.';
                $this->Flash->error($msg);
            } else {
                $userId = $this->Users->getIdWithEmail($email);
                if ($userId) {
                    if ($this->Users->sendPasswordResetEmail($userId)) {
                        $this->Flash->success('Success! You should be shortly receiving an email with a link to reset your password.');
                        $this->request->data = [];
                    } else {
                        $msg = 'There was an error sending your password-resetting email. ';
                        $msg .= 'Please try again, or email <a href="mailto:'.$adminEmail.'">'.$adminEmail.'</a> for assistance.';
                        $this->Flash->error($msg);
                    }
                } else {
                    $msg = 'We couldn\'t find an account registered with the email address <strong>'.$email.'</strong>. ';
                    $msg .= 'Please make sure you spelled it correctly, and email ';
                    $msg .= '<a href="mailto:'.$adminEmail.'">'.$adminEmail.'</a> if you need assistance.';
                    $this->Flash->error($msg);
                }
            }
        }
        $this->set([
            'titleForLayout' => 'Forgot Password',
            'user' => $user
        ]);
    }

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

        if ($this->request->is(['post', 'put'])) {
            $this->request->data['password'] = $this->request->data('new_password');
            $user = $this->Users->patchEntity($user, $this->request->data());
            if ($this->Users->save($user)) {
                $this->Flash->success('Your password has been updated.');
                return $this->redirect(['action' => 'login']);
            }
        }
        $this->request->data = [];

        $this->set([
            'titleForLayout' => 'Reset Password',
            'user' => $user
        ]);
    }
}
