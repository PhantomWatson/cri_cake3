<?php
namespace App\Test\TestCase\Alerts;

use App\Alerts\Alertable;
use App\Alerts\AlertSender;
use App\Model\Entity\Response;
use App\Model\Entity\Survey;
use App\Model\Table\CommunitiesTable;
use App\Model\Table\DeliverablesTable;
use App\Model\Table\DeliveriesTable;
use App\Model\Table\OptOutsTable;
use App\Model\Table\ProductsTable;
use App\Model\Table\PurchasesTable;
use App\Model\Table\ResponsesTable;
use App\Model\Table\SurveysTable;
use App\Model\Table\UsersTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Queue\Model\Table\QueuedJobsTable;

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
        'app.communities',
        'app.queued_jobs',
        'app.users'
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
