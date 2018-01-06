<?php
namespace App\Test\TestCase\Alerts;

use App\Alerts\Alert;
use App\Model\Entity\Community;
use App\Model\Entity\User;
use App\Model\Table\UsersTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Alerts\Alert Test Case
 * @property UsersTable $Users
 */
class AlertTest extends TestCase
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

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Users);

        parent::tearDown();
    }

    /**
     * Tests Alert::isRecentlySent()
     *
     * @return void
     * @throws \Exception
     */
    public function testIsRecentlySent()
    {
        /**
         * @var User $recipient
         * @var Community $community
         */
        $usersTable = TableRegistry::get('Users');
        $recipient = $usersTable->find()->first();
        $communitiesTable = TableRegistry::get('Communities');
        $community = $communitiesTable->find()->first();
        $mailerMethod = 'activateSurvey';
        $data = [
            'community' => ['id' => $community->id],
            'mailerMethod' => $mailerMethod,
            'surveyType' => 'official'
        ];

        $result = Alert::isRecentlySent($recipient->email, $community->id, $mailerMethod, $data['surveyType']);
        $this->assertFalse($result);

        Alert::enqueueEmail($recipient, $community, $data);

        $result = Alert::isRecentlySent($recipient->email, $community->id, $mailerMethod, $data['surveyType']);
        $this->assertTrue($result);
    }
}
