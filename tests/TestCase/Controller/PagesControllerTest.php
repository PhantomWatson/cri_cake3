<?php
namespace App\Test\TestCase\Controller;

use App\Controller\PagesController;
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

    public function setUp()
    {
        parent::setUp();
        $this->configRequest([
            'environment' => ['HTTPS' => 'on']
        ]);
    }

    public function testHome()
    {
        $this->get('/');
        $this->assertResponseOk();
    }

    public function testGlossary()
    {
        $this->get('/glossary');
        $this->assertResponseOk();
    }

    public function testFaqCommunity()
    {
        $this->get('/communityFAQ');
        $this->assertResponseOk();
    }

    public function testCredits()
    {
        $this->get('/credits');
        $this->assertResponseOk();
    }

    public function testEnroll()
    {
        $this->get('/enroll');
        $this->assertResponseCode(302);
    }
}
