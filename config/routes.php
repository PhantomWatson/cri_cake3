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

Router::defaultRouteClass('DashedRoute');

Router::prefix('admin', function ($routes) {
    $routes->fallbacks('DashedRoute');
});

Router::prefix('client', function ($routes) {
    $routes->fallbacks('DashedRoute');
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
    $routes->redirect('/client',            '/client/home');

    $routes->connect('/postback',           ['controller' => 'Purchases', 'action' => 'postback']);

    $routes->connect('/surveys/get_survey_list',           ['controller' => 'Surveys', 'action' => 'getSurveyList']);
    $routes->connect('/surveys/get_survey_url/*',          ['controller' => 'Surveys', 'action' => 'getSurveyUrl']);
    $routes->connect('/surveys/check_survey_assignment/*', ['controller' => 'Surveys', 'action' => 'checkSurveyAssignment']);
    $routes->connect('/surveys/get_qna_ids/*',             ['controller' => 'Surveys', 'action' => 'getQnaIds']);
    $routes->connect('/surveys/cron_import',               ['controller' => 'Surveys', 'action' => 'cronImport']);

    $routes->fallbacks('DashedRoute');
});

Plugin::routes();
