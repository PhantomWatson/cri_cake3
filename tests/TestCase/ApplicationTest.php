<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.3.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Test\TestCase;

use App\Application;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase;

/**
 * ApplicationTest class
 */
class ApplicationTest extends IntegrationTestCase
{

    public $adminUser = [
        'Auth' => [
            'User' => [
                'id' => 1,
                'role' => 'admin',
                'salutation' => '',
                'name' => 'Test Admin',
                'email' => 'testadmin@example.com',
                'phone' => '765-285-3399',
                'title' => 'Test',
                'organization' => 'Test',
                'password' => '$2y$10$oaedH1cbAvt/wayrRJlrVeMWtoQSzgBee81iivOmw4tjMlnvfdP/a',
                'all_communities' => true,
                'created' => '2013-10-16 11:49:38',
                'modified' => '2016-01-26 01:06:26'
            ]
        ]
    ];

    /**
     * testMiddleware
     *
     * @return void
     */
    public function testMiddleware()
    {
        $app = new Application(dirname(dirname(__DIR__)) . '/config');
        $middleware = new MiddlewareQueue();

        $middleware = $app->middleware($middleware);

        $this->assertInstanceOf(ErrorHandlerMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(AssetMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(RoutingMiddleware::class, $middleware->get(2));
    }

    /**
     * Asserts that a GET request to the provided URL results in a redirect to the login page
     *
     * @param string $url URL
     * @return void
     */
    public function assertRedirectToLogin($url)
    {
        $this->get($url);
        $this->assertRedirectContains(Router::url([
            'prefix' => false,
            'controller' => 'Users',
            'action' => 'login'
        ]));
    }
}
