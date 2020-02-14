<?php
declare(strict_types=1);

namespace App\Test\TestCase\Alerts;

use App\Alerts\AlertSender;
use App\Model\Table\UsersTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Exception;

/**
 * Class AlertableTest
 * @package App\Test\TestCase\Alerts
 * @property array $fixtures
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
    private $users;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->users = TableRegistry::getTableLocator()->get('Users');
    }

    /**
     * Test for AlertSender::isRecentlySent()
     *
     * @return void
     * @throws Exception
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
