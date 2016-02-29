<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Collection\Collection;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

/**
 * Communities Controller
 *
 * @property \App\Model\Table\CommunitiesTable $Communities
 */
class CommunitiesController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['autocomplete', 'index', 'view']);
    }

    public function beforeRender(\Cake\Event\Event $event)
    {
        parent::beforeRender($event);
        $this->viewBuilder()->helpers(['GoogleCharts.GoogleCharts']);
    }

    public function isAuthorized($user)
    {
        if ($this->request->action == 'view') {
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
            ->where([
                function ($q) {
                    return $q->notLike('name', 'Test %');
                }
            ])
            ->order(['Communities.name' => 'ASC'])
            ->toArray();
        $this->set([
            'communities' => $communities,
            'steps' => [
                'Sign up',
                'Community officials alignment assessment',
                'Community organizations alignment assessment',
                'Preliminary community readiness findings',
                'Community readiness report'
            ],
            'titleForLayout' => 'Community Progress'
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

        $community = $this->Communities->get($communityId, [
            'contain' => ['LocalAreas', 'ParentAreas']
        ]);

        if (! ($community->public || $this->isAuthorized($this->Auth->user()))) {
            $this->Flash->error('You are not authorized to access that community.');
            return $this->redirect('/');
        }

        if (! $this->Communities->exists(['id' => $communityId])) {
            throw new NotFoundException('Community not found');
        }

        $areasTable = TableRegistry::get('Areas');
        $areas = [];
        $barChart = [];
        $pwrTable = [];
        $lineChart = [];
        $growthTable = [];
        foreach (['local', 'parent'] as $areaScope) {
            $areaId = $community[$areaScope.'_area_id'];
            if ($areaId) {
                $areas[$areaScope] = $community[$areaScope.'_area']['name'];
            }
            $barChart[$areaScope] = $areasTable->getPwrBarChart($areaId);
            $pwrTable[$areaScope] = $areasTable->getPwrTable($areaId);
            $lineChart[$areaScope] = $areasTable->getEmploymentLineChart($areaId);
            $growthTable[$areaScope] = $areasTable->getEmploymentGrowthTableData($areaId);
        }
        $this->set([
            'titleForLayout' => $community->name.' Performance',
            'community' => $community,
            'areas' => $areas,
            'barChart' => $barChart,
            'pwrTable' => $pwrTable,
            'lineChart' => $lineChart,
            'growthTable' => $growthTable
        ]);
    }

    public function autocomplete()
    {
        $limit = 10;

        // Communities.name will be compared via LIKE to each of these until $limit communities are found.
        $patterns = [
            $_GET['term'],
            $_GET['term'].' %',
            $_GET['term'].'%',
            '% '.$_GET['term'].'%',
            '%'.$_GET['term'].'%'
        ];

        // Collect communities up to $limit
        $retval = [];
        foreach ($patterns as $pattern) {
            $results = $this->Communities->find('list')
                ->where([
                    function ($exp, $q) use ($pattern, $retval) {
                        $exp->like('name', $pattern);
                        if (! empty($retval)) {
                            $exp->notIn('id', array_keys($retval));
                        }
                        return $exp;
                    }
                ])
                ->limit($limit - count($retval))
                ->toArray();
            $retval += $results;
            if (count($retval) == $limit) {
                break;
            }
        }

        $this->set(['communities' => $retval]);
        $this->viewBuilder()->layout('json');
    }
}
