<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Network\Exception\NotFoundException;

/**
 * Communities Controller
 *
 * @property \App\Model\Table\CommunitiesTable $Communities
 */
class CommunitiesController extends AppController
{

    public function beforeRender(Event $event)
    {
        $this->getView()->loadHelper('GoogleCharts.GoogleCharts');
    }

    public function isAuthorized($user)
    {
        if ($this->action == 'view') {
            if (isset($this->request->pass[0]) && ! empty($this->request->pass[0])) {
                $communityId = $this->request->pass[0];
            } elseif (isset($_GET['cid']) && ! empty($_GET['cid'])) {
                $communityId = $_GET['cid'];
            } else {
                throw new NotFoundException('Community ID not specified');
            }
            $userId = isset($user['id']) ? $user['id'] : null;
            $usersTable = TableRegistry::get('Users');
            return $usersTable->canAccessCommunity($userId, $communityId);
        }

        return parent::isAuthorized($user);
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $communities = $this->Communities->find('all')
            ->select(['id', 'name', 'score'])
            ->where(['public' => true])
            ->order(['Community.name' => 'ASC']);
        $this->set([
            'communities' => $communities,
            'titleForLayout' => 'Indiana Communities'
        ]);
    }

    /**
     * View method
     *
     * @param string|null $communityId
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($communityId = null)
    {
        if (isset($_GET['cid'])) {
            $communityId = $_GET['cid'];
        }
        if (empty($communityId)) {
            throw new NotFoundException('Community ID not specified');
        }

        if (! $this->isAuthorized($this->Auth->user())) {
            $this->Flash->error('You are not authorized to access that community.');
            $this->redirect('/');
        }

        if (! $this->Communities->exists(['id' => $communityId])) {
            throw new NotFoundException('Community not found');
        }

        $community = $this->Communities->get($communityId);
        $this->set([
            'titleForLayout' => $community->name.' Performance',
            'community' => $community,
            'barChart' => $this->Communities->getPwrBarChart($communityId),
            'pwrTable' => $this->Communities->getPwrTable($communityId),
            'lineChart' => $this->Communities->getEmploymentLineChart($communityId),
            'growthTable' => $this->Communities->getEmploymentGrowthTableData($communityId)
        ]);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $community = $this->Communities->newEntity();
        if ($this->request->is('post')) {
            $community = $this->Communities->patchEntity($community, $this->request->data);
            if ($this->Communities->save($community)) {
                $this->Flash->success(__('The community has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The community could not be saved. Please, try again.'));
            }
        }
        $areas = $this->Communities->Areas->find('list', ['limit' => 200]);
        $this->set(compact('community', 'areas'));
        $this->set('_serialize', ['community']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Community id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $community = $this->Communities->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $community = $this->Communities->patchEntity($community, $this->request->data);
            if ($this->Communities->save($community)) {
                $this->Flash->success(__('The community has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The community could not be saved. Please, try again.'));
            }
        }
        $areas = $this->Communities->Areas->find('list', ['limit' => 200]);
        $this->set(compact('community', 'areas'));
        $this->set('_serialize', ['community']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Community id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $community = $this->Communities->get($id);
        if ($this->Communities->delete($community)) {
            $this->Flash->success(__('The community has been deleted.'));
        } else {
            $this->Flash->error(__('The community could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
