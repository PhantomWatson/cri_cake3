<?php
declare(strict_types=1);

namespace App\Mailer\Preview;

use DebugKit\Mailer\MailPreview;

class TestEmailPreview extends MailPreview
{
    /**
     * Preview method for TestMailer::test()
     *
     * @return \Cake\Mailer\Email
     */
    public function test()
    {
        return $this->getMailer('Test')
            ->test('recipient@example.com');
    }
}
