<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Test\TestCase;

use App\Application;
use App\Test\Fixture\UsersFixture;
use Cake\Database\Expression\QueryExpression;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Event\EventList;
use Cake\Http\MiddlewareQueue;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\ORM\TableRegistry;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase;
use InvalidArgumentException;

/**
 * ApplicationTest class
 */
class ApplicationTest extends IntegrationTestCase
{
    /**
     * Sets up this set of tests
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $usersFixture = new UsersFixture();
        $this->adminUser = [
            'Auth' => [
                'User' => $usersFixture->records[0]
            ]
        ];
        $this->clientUser = [
            'Auth' => [
                'User' => $usersFixture->records[1]
            ]
        ];
    }

    /**
     * testBootstrap
     *
     * @return void
     */
    public function testBootstrap()
    {
        $app = new Application(dirname(dirname(__DIR__)) . '/config');
        $app->bootstrap();
        $plugins = $app->getPlugins();

        $this->assertCount(3, $plugins);
        $this->assertSame('Bake', $plugins->get('Bake')->getName());
        $this->assertSame('Migrations', $plugins->get('Migrations')->getName());
        $this->assertSame('DebugKit', $plugins->get('DebugKit')->getName());
    }

    /**
     * testBootstrapPluginWitoutHalt
     *
     * @return void
     */
    public function testBootstrapPluginWitoutHalt()
    {
        $this->expectException(InvalidArgumentException::class);

        $app = $this->getMockBuilder(Application::class)
            ->setConstructorArgs([dirname(dirname(__DIR__)) . '/config'])
            ->setMethods(['addPlugin'])
            ->getMock();

        $app->method('addPlugin')
            ->will($this->throwException(new InvalidArgumentException('test exception.')));

        $app->bootstrap();
    }

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
     * @throws \PHPUnit\Exception
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

    /**
     * Asserts that an admin task email with $mailerMethod is enqueued
     *
     * @param string $mailerMethod Mailer method name
     * @return void
     */
    protected function assertAdminTaskEmailEnqueued($mailerMethod)
    {
        $count = $this->getAdminTaskEmailCount($mailerMethod);
        $msg = "At least one admin task email for the mailer method $mailerMethod() was expected, but none were found.";
        $this->assertGreaterThan(0, $this->getAdminTaskEmailCount($mailerMethod), $msg);
    }

    /**
     * Asserts that an admin task email with $mailerMethod is not enqueued
     *
     * @param string $mailerMethod Mailer method name
     * @return void
     */
    protected function assertAdminTaskEmailNotEnqueued($mailerMethod)
    {
        $count = $this->getAdminTaskEmailCount($mailerMethod);
        $msg = "No enqueued admin task emails for the mailer method $mailerMethod() were expected, but " .
            $count .
            __n(' was ', ' were ', $count) .
            'found.';
        $this->assertEquals(0, $count, $msg);
    }

    /**
     * Returns the count of enqueued email jobs containing $mailerMethod in their `data` fields
     *
     * @param string $mailerMethod Mailer method name
     * @return int
     */
    private function getAdminTaskEmailCount($mailerMethod)
    {
        $queuedJobsTable = TableRegistry::get('Queue.QueuedJobs');

        return $queuedJobsTable
            ->find()
            ->select(['id'])
            ->where(function ($exp) use ($mailerMethod) {
                /** @var QueryExpression $exp */

                return $exp->like('data', '%' . $mailerMethod . '%');
            })
            ->count();
    }

    /**
     * Overload controllerSpy() method in order to turn on event tracking
     *
     * @param \Cake\Event\Event $event A dispatcher event.
     * @param \Cake\Controller\Controller|null $controller Controller instance.
     * @return void
     */
    public function controllerSpy($event, $controller = null)
    {
        parent::controllerSpy($event, $controller);

        if (isset($this->_controller)) {
            $eventList = new EventList();
            $this->_controller->getEventManager()->setEventList($eventList);
        }
    }
}
