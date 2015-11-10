<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\TableRegistry;

class UsersController extends AppController
{
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
            'selectedCommunities' => $selectedCommunities,
            'user' => $user
        ]);
    }

    public function add()
    {
        $user = $this->Users->newEntity();

        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->request->data['new_password'] != '') {
                $user->password = $this->request->data['new_password'];
            }

            if (empty($this->request->data['client_communities'][0]['id'])) {
                $this->request->data['client_communities'] = [];
            }

            // Ignore ClientCommunity if user is not a client
            if ($this->request->data['role'] != 'client') {
                unset($this->request->data['client_communities']);
            }

            $user = $this->Users->patchEntity($user, $this->request->data);

            if ($this->Users->save($user)) {
                $this->Flash->success('User added');
                return $this->redirect([
                    'prefix' => 'admin',
                    'action' => 'index'
                ]);
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

    public function edit($id = null)
    {
        $user = $this->Users->get($id, ['contain' => ['ClientCommunities', 'ConsultantCommunities']]);

        if ($this->request->is('post') || $this->request->is('put')) {
            if (empty($this->request->data['client_communities'][0]['id'])) {
                $this->request->data['client_communities'] = [];
            }

            if (empty($this->request->data['consultant_communities'])) {
                $this->request->data['consultant_communities'] = [];
            }

            $user = $this->Users->patchEntity($user, $this->request->data());
            $errors = $user->errors();
            if (empty($errors)) {
                if ($this->request->data['new_password'] != '') {
                    $user->password = $this->request->data['new_password'];
                }

                $roleChanged = $user->dirty('role');

                if ($this->Users->save($user)) {
                    $msg = 'User info updated';
                    if ($roleChanged) {
                        $msg .= '. The update to this user\'s <strong>role</strong> will take effect the next time they manually log in or when their session automatically refreshes.';
                    }
                    $this->Flash->success($msg);
                    return $this->redirect([
                        'admin' => true,
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

    public function chooseClient()
    {
        $communitiesTable = TableRegistry::get('Communities');
        if ($this->request->is('post')) {
            $communityId = $this->request->data['community_id'];
            $this->Cookie->write('communityId', $communityId);
            $clientId = $communitiesTable->getCommunityClientId($communityId);
            $this->Cookie->write('clientId', $clientId);
            if (isset($this->request->data['redirect'])) {
                return $this->redirect($this->request->data['redirect']);
            } elseif ($this->request->is('ajax')) {
                $this->render('/Pages/blank');
                $this->viewBuilder()->layout('ajax');
            } else {
                $this->Flash->success('Client selected');
            }
        }
        $this->set([
            'communities' => $communitiesTable->getClientCommunityList(),
            'titleForLayout' => 'Choose client'
        ]);
        if (isset($this->request->query['redirect'])) {
            $this->set('redirect', urldecode($this->request->query['redirect']));
        }
    }
}
