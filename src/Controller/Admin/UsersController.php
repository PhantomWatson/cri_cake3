<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Mailer\Mailer;
use App\Model\Entity\User;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class UsersController extends AppController
{
    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $cookieParentKey = 'AdminUsersIndex';
        $cookieKey = "$cookieParentKey.filter";

        // Remember selected filters
        $filter = $this->request->query('filter');
        if ($filter) {
            $this->Cookie->write($cookieKey, $filter);

        // Use remembered filter when no filter is manually specified
        } elseif ($this->Cookie->check($cookieKey)) {
            $filter = $this->Cookie->read($cookieKey);
        }

        // Apply filters
        switch ($filter) {
            case 'client':
            case 'consultant':
            case 'admin':
                $this->paginate['conditions']['Users.role'] = $filter;
                break;
            default:
                $filter = 'all';
                break;
        }

        $this->set([
            'titleForLayout' => 'Users',
            'users' => $this->paginate(),
            'buttons' => [
                'all' => 'All Users',
                'client' => 'Clients',
                'consultant' => 'Consultants',
                'admin' => 'Admins'
            ],
            'currentFilter' => $filter
        ]);
    }

    /**
     * Sets variables in the view for the user add/edit form
     *
     * @param User $user User
     * @return void
     */
    private function prepareForm($user)
    {
        $communities = $this->Users->ConsultantCommunities
            ->find('list')
            ->order('name')
            ->toArray();
        $selectedCommunities = [];
        if (! empty($user->consultant_communities)) {
            foreach ($user->consultant_communities as $community) {
                $selectedCommunities[] = [
                    'id' => $community->id,
                    'name' => $communities[$community->id]
                ];
            }
        }
        $noCommunity = [0 => '(No community)'];
        $communities = $noCommunity + $communities;

        $this->request->data['new_password'] = '';
        $this->request->data['confirm_password'] = '';

        $this->set([
            'communities' => $communities,
            'roles' => [
                'admin' => 'Admin',
                'client' => 'Client',
                'consultant' => 'Consultant'
            ],
            'salutations' => $this->Users->getSalutations(),
            'selectedCommunities' => $selectedCommunities,
            'user' => $user
        ]);
    }

    /**
     * Add method
     *
     * @return \App\Controller\Response
     */
    public function add()
    {
        $user = $this->Users->newEntity();

        if ($this->request->is('post') || $this->request->is('put')) {
            $this->request->data['password'] = $this->request->data['new_password'];

            if (empty($this->request->data['client_communities'][0]['id'])) {
                $this->request->data['client_communities'] = [];
            }

            // Ignore ClientCommunity if user is not a client
            if ($this->request->data['role'] != 'client') {
                unset($this->request->data['client_communities']);
            }

            $user = $this->Users->patchEntity($user, $this->request->data);

            $errors = $user->errors();
            if (empty($errors) && $this->Users->save($user)) {
                // Set as the returnPath for invitation emails
                $senderEmail = $this->Auth->user('email');
                $senderName = $this->Auth->user('name');

                $Mailer = new Mailer();
                $result = $Mailer->sendNewAccountEmail(
                    $user,
                    $this->request->data['new_password'],
                    $senderEmail,
                    $senderName
                );
                if ($result) {
                    $this->Flash->success('User account created and login credentials emailed');
                    return $this->redirect([
                        'prefix' => 'admin',
                        'action' => 'index'
                    ]);
                } else {
                    $this->Users->delete($user);
                    $msg = 'There was an error emailing this user with their login info. No new account was created.';
                    $msg .= ' Please try again or contact an administrator for assistance.';
                    $this->Flash->error($msg);
                }
            } else {
                $msg = 'There was an error creating this user\'s account.';
                $msg .= ' Please try again or contact an administrator for assistance.';
                $this->Flash->error($msg);
            }
        } else {
            $this->request->data['all_communities'] = false;
        }

        $this->prepareForm($user);
        $this->set([
            'titleForLayout' => 'Add User'
        ]);
        $this->render('/Admin/Users/form');
    }

    /**
     * Edit method
     *
     * @param int|null $id User ID
     * @return \Cake\Network\Response|null
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, ['contain' => ['ClientCommunities', 'ConsultantCommunities']]);

        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->request->data['new_password'] != '') {
                $this->request->data['password'] = $this->request->data['new_password'];
            }

            if (empty($this->request->data['client_communities'][0]['id'])) {
                $this->request->data['client_communities'] = [];
            }

            if (empty($this->request->data['consultant_communities'])) {
                $this->request->data['consultant_communities'] = [];
            }

            $user = $this->Users->patchEntity($user, $this->request->data());
            $errors = $user->errors();
            if (empty($errors)) {
                $roleChanged = $user->dirty('role');

                if ($this->Users->save($user)) {
                    $msg = 'User info updated';
                    if ($roleChanged) {
                        $msg .= '. The update to this user\'s <strong>role</strong> will take effect';
                        $msg .= ' the next time they manually log in or when their session automatically refreshes.';
                    }
                    $this->Flash->success($msg);
                    return $this->redirect([
                        'prefix' => 'admin',
                        'action' => 'index'
                    ]);
                }
            } else {
                $this->Flash->error('Please correct the indicated error(s)');
            }
        }

        $this->prepareForm($user);
        $this->set([
            'titleForLayout' => $user->name
        ]);
        $this->render('/Admin/Users/form');
    }

    /**
     * Delete method
     *
     * @param int|null $id User ID
     * @return \Cake\Network\Response|null
     */
    public function delete($id = null)
    {
        if (! $this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $user = $this->Users->get($id);

        if ($this->Users->delete($user)) {
            $this->Flash->success('User deleted');
        } else {
            $this->Flash->error('User was not deleted');
        }
        return $this->redirect([
            'prefix' => 'admin',
            'action' => 'index'
        ]);
    }

    /**
     * ChooseClient method
     *
     * @return \Cake\Network\Response|null
     */
    public function chooseClient()
    {
        $communitiesTable = TableRegistry::get('Communities');
        if ($this->request->is('post')) {
            $communityId = $this->request->data['community_id'];
            $this->Cookie->write('communityId', $communityId);
            $clientId = $communitiesTable->getCommunityClientId($communityId);
            $this->Cookie->write('clientId', $clientId);
            if ($this->request->data('redirect')) {
                return $this->redirect($this->request->data['redirect']);
            } elseif ($this->request->is('ajax')) {
                $this->render('/Pages/blank');
                $this->viewBuilder()->layout('ajax');
            } else {
                $this->Flash->success('Client selected');
                return $this->redirect([
                    'prefix' => 'client',
                    'controller' => 'Communities',
                    'action' => 'index'
                ]);
            }
        }
        $this->set([
            'communities' => $communitiesTable->getClientCommunityList(),
            'redirect' => urldecode($this->request->query('redirect')),
            'titleForLayout' => 'Choose client'
        ]);
    }
}
