<?php
declare(strict_types=1);

namespace App\Test\TestCase\Alerts;

use App\Alerts\AlertSender;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Class AlertableTest
 * @package App\Test\TestCase\Alerts
 * @property array $fixtures
 * @property CommunitiesTable $communities
 * @property UsersTable $users
 */
class AlertSenderTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Communities',
        'app.QueuedJobs',
        'app.Users',
    ];
    private $communities;
    private $users;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->communities = TableRegistry::get('Communities');
        $this->users = TableRegistry::get('Users');
    }

    /**
     * Test for AlertSender::isRecentlySent()
     *
     * @return void
     * @throws \Exception
     */
    public function testIsRecentlySent()
    {
        $communityId = 1;
        $sender = new AlertSender($communityId);
        $alertName = 'deliverPresentationA';
        $userId = 1;
        $recipient = $this->users->get($userId);
        $result = (bool)$sender->isRecentlySent($recipient->email, $alertName);
        $this->assertFalse($result);

        // Send alert
        $sender->enqueueEmail($recipient, $alertName);
        $result = (bool)$sender->isRecentlySent($recipient->email, $alertName);
        $this->assertTrue($result);
    }
}
