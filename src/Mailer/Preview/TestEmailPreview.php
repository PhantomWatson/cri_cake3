<?php
namespace App\Mailer\Preview;

use Cake\Mailer\Email;
use DebugKit\Mailer\MailPreview;

class TestEmailPreview extends MailPreview
{
    /**
     * Preview method for TestMailer::test()
     *
     * @return Email
     */
    public function test()
    {
        return $this->getMailer('Test')
            ->test('recipient@example.com');
    }
}
