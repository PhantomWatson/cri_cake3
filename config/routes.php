<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Plugin;
use Cake\Routing\Router;

/**
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 *
 */
Router::defaultRouteClass('Route');

Router::prefix('admin', function ($routes) {
    $routes->fallbacks('InflectedRoute');
});

Router::prefix('client', function ($routes) {
    $routes->fallbacks('InflectedRoute');
});

Router::scope('/', function ($routes) {
    $routes->connect('/',                   ['controller' => 'Pages', 'action' => 'home']);
    $routes->connect('/glossary',           ['controller' => 'Pages', 'action' => 'glossary']);
    $routes->connect('/credits',            ['controller' => 'Pages', 'action' => 'credits']);
    $routes->connect('/enroll',             ['controller' => 'Pages', 'action' => 'enroll']);
    $routes->connect('/fasttrack',          ['controller' => 'Pages', 'action' => 'fasttrack']);
    $routes->connect('/faq',                ['controller' => 'Pages', 'action' => 'home']);
    $routes->connect('/communityFAQ',       ['controller' => 'Pages', 'action' => 'faqCommunity']);
    $routes->connect('/consultantFAQ',      ['controller' => 'Pages', 'action' => 'faqConsultants']);
    $routes->connect('/clear_cache',        ['controller' => 'Pages', 'action' => 'clearCache']);
    $routes->connect('/guide',              ['prefix' => 'admin', 'controller' => 'Pages', 'action' => 'guide']);
    $routes->redirect('/consultantfaq',     '/consultantFAQ');
    $routes->redirect('/communityfaq',      '/communityFAQ');

    $routes->connect('/login',              ['controller' => 'Users', 'action' => 'login']);
    $routes->connect('/logout',             ['controller' => 'Users', 'action' => 'logout']);
    $routes->connect('/update_contact',     ['controller' => 'Users', 'action' => 'updateContact']);
    $routes->connect('/change_password',    ['controller' => 'Users', 'action' => 'changePassword']);
    $routes->connect('/admin/choose_client',['prefix' => 'admin', 'controller' => 'Users', 'action' => 'chooseClient']);

    $routes->connect('/community/:id',      ['controller' => 'Communities', 'action' => 'view'], ['id' => '\d+', 'pass' => ['id']]);
    $routes->connect('/client/home',        ['prefix' => 'client', 'controller' => 'Communities', 'action' => 'index']);

    $routes->connect('/postback',           ['controller' => 'Purchases', 'action' => 'postback']);

    $routes->connect('/surveys/get_survey_list', ['controller' => 'Surveys', 'action' => 'getSurveyList']);
    $routes->connect('/surveys/get_survey_url', ['controller' => 'Surveys', 'action' => 'getSurveyUrl']);


    /**
     * Connect catchall routes for all controllers.
     *
     * Using the argument `InflectedRoute`, the `fallbacks` method is a shortcut for
     *    `$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'InflectedRoute']);`
     *    `$routes->connect('/:controller/:action/*', [], ['routeClass' => 'InflectedRoute']);`
     *
     * Any route class can be used with this method, such as:
     * - DashedRoute
     * - InflectedRoute
     * - Route
     * - Or your own route class
     *
     * You can remove these routes once you've connected the
     * routes you want in your application.
     */
    $routes->fallbacks('InflectedRoute');
});

/**
 * Load all plugin routes.  See the Plugin documentation on
 * how to customize the loading of plugin routes.
 */
Plugin::routes();
