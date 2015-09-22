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
        if ($filter) {
            switch ($filter) {
                case 'client':
                case 'consultant':
                case 'admin':
                    $this->paginate['conditions']['Users.role'] = $filter;
                    break;
                default:
                    // No action
                    break;
            }
        }

        $this->set([
            'titleForLayout' => 'Users',
            'users' => $this->paginate(),
            'buttons' => [
                'all' => 'All Users',
                'client' => 'Clients',
                'consultant' => 'Consultants',
                'admin' => 'Admins'
            ]
        ]);
    }

    public function add()
    {
        $user = $this->Users->newEntity();

        if ($this->request->is('post') || $this->request->is('put')) {
            // Force numerically-indexed array
            if (! empty($this->request->data['consultant_communities'])) {
                $this->request->data['consultant_communities'] = array_values($this->request->data['consultant_communities']);
            }

            // Ignore ClientCommunity if user is not a client
            if ($this->request->data['role'] != 'client') {
                unset($this->request->data['client_communities']);
            }

            $user = $this->Users->patchEntity($user, $this->request->data);

            if ($this->Users->save($user)) {
                $this->Flash->success('User added');
                $this->redirect([
                    'prefix' => 'admin',
                    'action' => 'index'
                ]);
            }
        } else {
            $this->request->data['all_communities'] = false;
        }

        // Prepare selected communities for JS
        $communities = $this->Users->ConsultantCommunities->find('list');
        $selectedCommunities = [];
        if (isset($this->request->data['consultant_communities'])) {
            foreach ($this->request->data['consultant_communities'] as $communityId) {
                $selectedCommunities[] = [
                    'id' => $communityId,
                    'name' => $communities[$communityId]
                ];
            }
        }

        $this->set([
            'communities' => $communities,
            'roles' => [
                'admin' => 'Admin',
                'client' => 'Client',
                'consultant' => 'Consultant'
            ],
            'selectedCommunities' => $selectedCommunities,
            'titleForLayout' => 'Add User',
            'user' => $user
        ]);
        $this->render('/Admin/Users/form');
    }

    public function edit($id = null)
    {
        $user = $this->Users->get($id);

        if ($this->request->is('post') || $this->request->is('put')) {
            $user = $this->Users->patchEntity($user, $this->request->data(), [
                'validate' => 'WithoutPassword'
            ]);

            $errors = $user->errors();
            if (empty($errors)) {
                if ($this->request->data['new_password'] != '') {
                    $user->password = $this->request->data['new_password'];
                }

                // Force numerically-indexed array
                if (! empty($this->request->data['consultant_community'])) {
                    $user->consultant_community = array_values($this->request->data['consultant_community']);
                }

                if ($this->Users->save($user)) {
                    $this->Flash->success('User info updated');
                    $this->redirect([
                        'admin' => true,
                        'action' => 'index'
                    ]);
                }
            } else {
                $this->Flash->error('Please correct the indicated error(s)');
            }
        } else {
            $this->request->data = $this->Users->find('all')
                ->select(['name'])
                ->where(['Users.id' => $id])
                ->contain([
                    'ConsultantCommunities',
                    'ClientCommunities'
                ])
                ->first()
                ->toArray();
        }

        // Clear password fields
        $this->request->data['password'] = '';
        $this->request->data['confirm_password'] = '';

        // Prepare selected communities for JS
        $communities = $this->Users->ConsultantCommunities->find('list');
        $selectedCommunities = [];
        if (! empty($this->request->data['consultant_community'])) {
            foreach ($this->request->data['consultant_community'] as $community) {
                $communityId = isset($community['id']) ? $community['id'] : $community;
                $selectedCommunities[] = [
                    'id' => $communityId,
                    'name' => $communities[$communityId]
                ];
            }
        }

        $this->set([
            'communities' => $communities,
            'roles' => [
                'admin' => 'Admin',
                'client' => 'Client',
                'consultant' => 'Consultant'
            ],
            'selectedCommunities' => $selectedCommunities,
            'titleForLayout' => $this->request->data['name'],
            'user' => $user
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
        $this->redirect([
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
                $this->redirect($this->request->data['redirect']);
            } elseif ($this->request->is('ajax')) {
                $this->render('/Pages/blank');
                $this->layout = 'ajax';
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
