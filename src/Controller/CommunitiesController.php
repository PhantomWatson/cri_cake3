<?php
namespace App\Controller;

use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Muffin\Slug\Slugger\CakeSlugger;

/**
 * Communities Controller
 *
 * @property \App\Model\Table\CommunitiesTable $Communities
 */
class CommunitiesController extends AppController
{

    /**
     * initialize method
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['autocomplete', 'index', 'view']);
    }

    /**
     * beforeRender method
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeRender(\Cake\Event\Event $event)
    {
        parent::beforeRender($event);
        $this->viewBuilder()->setHelpers(['GoogleCharts.GoogleCharts']);
    }

    /**
     * isAuthorized method
     *
     * @param array $user User
     * @return bool
     * @throws NotFoundException
     */
    public function isAuthorized($user)
    {
        if ($this->request->action == 'view') {
            if (isset($this->request->pass[0]) && ! empty($this->request->pass[0])) {
                $communitySlug = $this->request->pass[0];
            } else {
                $communitySlug = $this->request->getQuery('community_slug');
                if (! $communitySlug) {
                    throw new NotFoundException('Community not specified');
                }
            }
            $community = $this->Communities->find('slugged', ['slug' => $communitySlug])->first();
            $userId = isset($user['id']) ? $user['id'] : null;
            $usersTable = TableRegistry::get('Users');

            return $usersTable->canAccessCommunity($userId, $community);
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
     * @param string|null $communitySlug Community slug
     * @return \Cake\Http\Response|null
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($communitySlug = null)
    {
        if ($this->request->getQuery('community_slug')) {
            $communitySlug = $this->request->getQuery('community_slug');
        }
        if (empty($communitySlug)) {
            throw new NotFoundException('Community not specified');
        }

        $community = $this->Communities->find('slugged', ['slug' => $communitySlug])
            ->contain(['LocalAreas', 'ParentAreas'])
            ->first();

        if (! $community) {
            throw new NotFoundException('Community not found');
        }

        if (! ($community->public || $this->isAuthorized($this->Auth->user()))) {
            $this->Flash->error('You are not authorized to access that community.');

            return $this->redirect('/', 403);
        }

        $areasTable = TableRegistry::get('Areas');
        $areas = [];
        $barChart = [];
        $pwrTable = [];
        $lineChart = [];
        $growthTable = [];
        foreach (['local', 'parent'] as $areaScope) {
            $areaId = $community[$areaScope . '_area_id'];
            if ($areaId) {
                $areas[$areaScope] = $community[$areaScope . '_area']['name'];
            }
            $barChart[$areaScope] = $areasTable->getPwrBarChart($areaId);
            $pwrTable[$areaScope] = $areasTable->getPwrTable($areaId);
            $lineChart[$areaScope] = $areasTable->getEmploymentLineChart($areaId);
            $growthTable[$areaScope] = $areasTable->getEmploymentGrowthTableData($areaId);
        }
        $this->set([
            'titleForLayout' => $community->name . ' Performance',
            'community' => $community,
            'areas' => $areas,
            'barChart' => $barChart,
            'pwrTable' => $pwrTable,
            'lineChart' => $lineChart,
            'growthTable' => $growthTable
        ]);
    }

    /**
     * Method for /communities/autocomplete
     *
     * @return void
     */
    public function autocomplete()
    {
        $limit = 10;

        $term = $this->request->getQuery('term');

        if ($term === null) {
            throw new NotFoundException('No term provided for autocomplete');
        }

        // Communities.name will be compared via LIKE to each of these until $limit communities are found.
        $patterns = [
            $term,
            "$term %",
            "$term%",
            "% $term%",
            "%$term%"
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
        $this->viewBuilder()->setLayout('json');
    }

    /**
     * Adds any missing slugs in the communities table
     *
     * @return \Cake\Http\Response
     */
    public function slug()
    {
        $communities = $this->Communities->find()
            ->select(['id', 'name'])
            ->where([
                function ($q) {
                    return $q->isNull('slug');
                }
            ]);
        $slugger = new CakeSlugger();
        foreach ($communities as $community) {
            $slug = $slugger->slug($community->name);
            $community = $this->Communities->patchEntity($community, [
                'slug' => $slug
            ]);
            if ($this->Communities->save($community)) {
                $this->Flash->success("Slug $slug added for community $community->name");
            } else {
                $this->Flash->error("Couldn't add slug $slug for community $community->name");
            }
        }

        return $this->redirect('/');
    }
}
