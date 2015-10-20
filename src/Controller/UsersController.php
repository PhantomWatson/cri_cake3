<?php
namespace App\Controller;

use App\Controller\AppController;

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
        $this->Auth->allow(['login', 'logout']);
    }

    public function login()
    {
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
                if ($this->Auth->authenticationProvider()->needsPasswordRehash()) {
                    $user = $this->Users->get($this->Auth->user('id'));
                    $user->password = $this->request->data('password');
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
            $user->password = $this->request->data['new_password'];
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
}
