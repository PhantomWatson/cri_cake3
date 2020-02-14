<?php
declare(strict_types=1);

namespace App\Test\TestCase\Event;

use App\Event\EmailListener;
use App\Test\TestCase\ApplicationTest;

class EmailListenerTest extends ApplicationTest
{
    public $fixtures = [
        'app.ClientsCommunities',
        'app.Communities',
        'app.Deliverables',
        'app.Deliveries',
        'app.QueuedJobs',
        'app.Surveys',
        'app.Users',
    ];

    /**
     * SetUp method
     *
     * @return void
     */
    public function setUp(): void
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
            'Model.Community.afterScoreIncrease' => 'sendCommunityPromotedEmail',
        ];
        $listener = new EmailListener();
        $actual = $listener->implementedEvents();
        foreach ($required as $event => $method) {
            $this->assertArrayHasKey($event, $actual);
            $this->assertEquals($method, $actual[$event]);
        }
    }
}
