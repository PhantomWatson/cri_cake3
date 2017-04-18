<?php
namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\PagesController Test Case
 */
class PagesControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [];

    /**
     * Sets up this set of tests
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->configRequest([
            'environment' => ['HTTPS' => 'on']
        ]);
    }

    /**
     * Test for /pages/home
     *
     * @return void
     */
    public function testHome()
    {
        $this->get('/');
        $this->assertResponseOk();
    }

    /**
     * Test for /pages/glossary
     *
     * @return void
     */
    public function testGlossary()
    {
        $this->get('/glossary');
        $this->assertResponseOk();
    }

    /**
     * Test for /pages/faq-community
     *
     * @return void
     */
    public function testFaqCommunity()
    {
        $this->get('/communityFAQ');
        $this->assertResponseOk();
    }

    /**
     * Test for /pages/credits
     *
     * @return void
     */
    public function testCredits()
    {
        $this->get('/credits');
        $this->assertResponseOk();
    }

    /**
     * Test for /pages/enroll
     *
     * @return void
     */
    public function testEnroll()
    {
        $this->get('/enroll');
        $this->assertResponseCode(302);
    }

    /**
     * Test for /pages/maintenance
     *
     * @return void
     */
    public function testMaintenance()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /pages/send-test-email
     *
     * @return void
     */
    public function testSendTestEmail()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test for /admin/pages/guide
     *
     * @return void
     */
    public function testAdminGuide()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
