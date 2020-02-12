<?php
namespace App\Test\TestCase\Event;

use App\Event\EmailListener;
use App\Test\TestCase\ApplicationTest;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

class EmailListenerTest extends ApplicationTest
{
    public $fixtures = [
        'app.ClientsCommunities',
        'app.Communities',
        'app.Deliverables',
        'app.Deliveries',
        'app.QueuedJobs',
        'app.Surveys',
        'app.Users'
    ];

    /**
     * SetUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Tests that EmailListener::implementedEvents() contains all required triggers
     *
     * @return void
     */
    public function testImplementedEvents()
    {
        $required = [
            'Model.Community.afterAutomaticAdvancement' => 'sendCommunityPromotedEmail',
            'Model.Community.afterScoreIncrease' => 'sendCommunityPromotedEmail'
        ];
        $listener = new EmailListener();
        $actual = $listener->implementedEvents();
        foreach ($required as $event => $method) {
            $this->assertArrayHasKey($event, $actual);
            $this->assertEquals($method, $actual[$event]);
        }
    }
}
