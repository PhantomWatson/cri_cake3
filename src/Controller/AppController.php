<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Event\ActivityRecordsListener;
use App\Event\EmailListener;
use App\Event\SurveysListener;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Hash;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 * @property \DataCenter\Controller\Component\FlashComponent $Flash
 */
class AppController extends Controller
{

    public $helpers = ['Tools.Time'];

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     * @throws \Exception
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');

        $this->loadComponent('DataCenter.Flash');

        $this->loadComponent('Security', [
            'blackHoleCallback' => 'forceSSL',
            'validatePost' => false
        ]);

        $this->loadComponent('Cookie', [
            'encryption' => 'aes',
            'key' => Configure::read('cookie_key')
        ]);

        $this->loadAuthComponent();

        // Prevents cookies from being accessible in Javascript
        $this->Cookie->httpOnly = true;

        // Set up listeners
        $activityRecordsListener = new ActivityRecordsListener();
        $activityRecordsListener->userId($this->Auth->user('id'));
        EventManager::instance()->on($activityRecordsListener);
        $surveysListener = new SurveysListener();
        EventManager::instance()->on($surveysListener);
        $emailListener = new EmailListener();
        EventManager::instance()->on($emailListener);

        /*
         * Enable the following components for recommended CakePHP security settings.
         * see http://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        //$this->loadComponent('Csrf');
    }

    /**
     * Loads and configures the Auth component
     *
     * @return void
     * @throws \Exception
     */
    public function loadAuthComponent()
    {
        $this->loadComponent('Auth', [
            'loginAction' => [
                'prefix' => false,
                'plugin' => false,
                'controller' => 'Users',
                'action' => 'login'
            ],
            'logoutRedirect' => [
                'prefix' => false,
                'plugin' => false,
                'controller' => 'Pages',
                'action' => 'home'
            ],
            'authenticate' => [
                'Form' => [
                    'fields' => ['username' => 'email'],
                    'passwordHasher' => [
                        'className' => 'Fallback',
                        'hashers' => ['Default', 'Legacy']
                    ]
                ],
                'Xety/Cake3CookieAuth.Cookie' => [
                    'cookie' => [
                        'name' => 'CookieAuth'
                    ],
                    'fields' => ['username' => 'email']
                ]
            ],
            'authorize' => ['Controller']
        ]);
        $this->Auth->deny();
        $errorMessage = $this->Auth->user() ?
            'Sorry, you are not authorized to access that page.'
            : 'Please log in before accessing that page.';
        $this->Auth->setConfig('authError', $errorMessage);
    }

    /**
     * beforeFilter method
     *
     * @param \Cake\Event\Event $event Event
     * @return \Cake\Http\Response|null
     */
    public function beforeFilter(Event $event)
    {
        $this->Security->requireSecure();

        // Set accessible communities
        $usersTable = TableRegistry::get('Users');
        $this->set([
            'accessibleCommunities' => $usersTable->getAccessibleCommunities($this->Auth->user('id'))
        ]);

        // Automatically log in
        if (! $this->Auth->user() && $this->Cookie->read('CookieAuth')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
            } else {
                $this->Cookie->delete('CookieAuth');
            }
        }

        if (Configure::read('maintenance_mode')) {
            $allowedActions = [
                'maintenance',
                'home',
                'faqCommunity',
                'enroll',
                'credits',
                'glossary',
                'sendTestEmail'
            ];
            if (!in_array($this->request->getParam('action'), $allowedActions)) {
                return $this->redirect([
                    'prefix' => false,
                    'controller' => 'Pages',
                    'action' => 'maintenance'
                ]);
            }
        }
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Network\Response|null|void
     */
    public function beforeRender(Event $event)
    {
        $this->setLayoutVariables();

        if ($this->Auth->user('role') == 'admin') {
            $this->prepareAdminHeader();
        }

        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->getType(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
    }

    /**
     * isAuthorized method
     *
     * @param array $user User
     * @return bool
     */
    public function isAuthorized($user)
    {
        if (! isset($user['role'])) {
            return false;
        }

        // Admin can access every action
        if ($user['role'] === 'admin') {
            return true;
        }

        // Clients and consultants can access the respective role-prefixed actions
        $prefix = $this->request->getParam('prefix');

        return $prefix === $user['role'];
    }

    /**
     * Redirects to SSL version of page
     *
     * @return \Cake\Http\Response|null
     */
    public function forceSSL()
    {
        return $this->redirect('https://' . env('SERVER_NAME') . $this->request->getRequestTarget());
    }

    /**
     * Redirects (returns a redirect Response object) to the page used by admins for choosing a client to impersonate
     *
     * @return \Cake\Http\Response
     * @throws ForbiddenException
     */
    protected function chooseClientToImpersonate()
    {
        if ($this->Auth->user('role') != 'admin') {
            throw new ForbiddenException('Error: Client ID not found for ' . $this->Auth->user('role') . ' account');
        }

        return $this->redirect([
            'prefix' => 'admin',
            'controller' => 'Users',
            'action' => 'chooseClient',
            'redirect' => urlencode(Router::url([]))
        ]);
    }

    /**
     * Return the logged-in client's ID, the previously-remembered client ID,
     * or null if neither is possible.
     * @return int|null
     */
    protected function getClientId()
    {
        if ($this->Auth->user('role') == 'client') {
            return $this->Auth->user('id');
        }

        // Admins can set the ID of the client they're impersonating
        $clientId = $this->Cookie->read('clientId');
        if ($clientId) {
            return $clientId;
        }

        return null;
    }

    /**
     * Accepts an array of stringy variables and returns a comma-delimited list with an optional conjunction before the last element
     *
     * @param array $array Array to turn into a string
     * @param string $conjunction Conjunction, defaults to 'and'
     * @return string
     */
    protected function arrayToList($array, $conjunction = 'and')
    {
        $count = count($array);
        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return $array[0];
        }

        if ($conjunction) {
            $lastElement = array_pop($array);
            array_push($array, $conjunction . ' ' . $lastElement);
        }

        if ($count == 2) {
            return implode(' ', $array);
        }

        return implode(', ', $array);
    }

    /**
     * Sets variables used in the default layout
     *
     * @return void
     */
    public function setLayoutVariables()
    {
        // Set up variables for sidebar
        if ($this->viewBuilder()->getLayout() == 'default' && $this->Auth->user('role') == 'admin') {
            $communitiesTable = TableRegistry::get('Communities');
            $this->set([
                'sidebar' => [
                    'communities' => $communitiesTable->getClientCommunityList(),
                    'communityId' => $this->Cookie->read('communityId'),
                    'clientId' => $this->Cookie->read('clientId')
                ]
            ]);
        }

        $this->set([
            'authUser' => $this->Auth->user()
        ]);
    }

    /**
     * Sets the $adminHeader variable
     *
     * @return void
     */
    public function prepareAdminHeader()
    {
        $this->loadModel('Communities');
        $communities = $this->Communities->find()
            ->select(['id', 'name', 'slug'])
            ->order(['name' => 'ASC'])
            ->all();

        $route = [
            'prefix' => 'admin',
            'plugin' => false,
            'controller' => 'Communities'
        ];
        $communityPages = [
            'Edit' => Router::url($route + ['action' => 'edit']) . '/{community-slug}',
            'Progress' => Router::url($route + ['action' => 'progress']) . '/{community-slug}',
            'Clients' => Router::url($route + ['action' => 'clients']) . '/{community-slug}',
            'Client Home' => Router::url($route + ['action' => 'clienthome']) . '/{community-slug}',
            'Presentations' => Router::url($route + ['action' => 'presentations']) . '/{community-slug}',
            'Notes' => Router::url($route + ['action' => 'notes']) . '/{community-slug}',
            'Purchases' => Router::url([
                'prefix' => 'admin',
                'plugin' => false,
                'controller' => 'Purchases',
                'action' => 'view'
            ]) . '/{community-slug}',
            'Activity' => Router::url([
                'prefix' => 'admin',
                'plugin' => false,
                'controller' => 'ActivityRecords',
                'action' => 'community'
            ]) . '/{community-id}',
            '(De)activate' => Router::url($route + ['action' => 'activate']) . '/{community-slug}'
        ];

        $route = [
            'prefix' => 'admin',
            'plugin' => false,
        ];
        $surveyPages = [
            'Overview' => Router::url($route + ['controller' => 'Surveys', 'action' => 'view']) . '/{community-slug}/{survey-type}',
            'Link' => Router::url($route + ['controller' => 'Surveys', 'action' => 'link']) . '/{community-slug}/{survey-type}',
            'Activate' => Router::url($route + ['controller' => 'Surveys', 'action' => 'activate']) . '/{survey-id}',
            'Invitations' => Router::url($route + ['controller' => 'Surveys', 'action' => 'invite']) . '/{survey-id}',
            'Reminders' => Router::url($route + ['controller' => 'Surveys', 'action' => 'remind']) . '/{survey-id}',
            'Respondents' => Router::url($route + ['controller' => 'Respondents', 'action' => 'view']) . '/{survey-id}',
            'Alignment' => Router::url($route + ['controller' => 'Responses', 'action' => 'view']) . '/{survey-id}'
        ];

        $this->loadModel('Surveys');
        $results = $this->Surveys->find('all')
            ->select(['id', 'type', 'community_id'])
            ->toArray();
        $surveyIds = Hash::combine($results, '{n}.type', '{n}.id', '{n}.community_id');

        $slugs = Hash::combine($communities->toArray(), '{n}.id', '{n}.slug');

        $this->set([
            'adminHeader' => [
                'communities' => $communities,
                'communityPages' => $communityPages,
                'currentUrl' => '/' . $this->request->getPath(),
                'slugs' => $slugs,
                'surveyIds' => $surveyIds,
                'surveyPages' => $surveyPages
            ]
        ]);
    }
}
