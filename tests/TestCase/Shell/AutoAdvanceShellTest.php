<?php
declare(strict_types=1);

namespace App\Test\TestCase\Shell;

use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\TestSuite\ConsoleIntegrationTestCase;

class AutoAdvanceShellTest extends ConsoleIntegrationTestCase
{
    public $fixtures = [
        'app.Communities',
        'app.QueuedJobs',
        'app.Surveys',
        'app.OptOuts',
        'app.Purchases',
        'app.Responses',
        'app.Respondents',
        'app.ActivityRecords',
        'app.Users',
        'app.ClientsCommunities',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        EventManager::instance()->setEventList(new EventList());
    }

    /**
     * Tests automatically advancing community #1 from step 1 to 2
     *
     * Assumes that fixture data qualifies this community for advancement
     *
     * @return void
     */
    public function testAdvanceToStepTwo()
    {
        $this->exec('auto_advance run');
        $this->assertOutputContains('<success>Advanced Test Community (public) to Step 2</success>');
    }

    /**
     * Tests that automatic advancement correctly dispatches an event
     *
     * Assumes that fixture data qualifies at least one community for advancement
     *
     * @return void
     */
    public function testAdvanceEvent()
    {
        $this->exec('auto_advance run');
        $this->assertEventFired('Model.Community.afterAutomaticAdvancement');
    }
}
