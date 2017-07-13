<?php
namespace App\Mailer;

use Cake\Mailer\Email;
use Cake\Mailer\Mailer;

class TestMailer extends Mailer
{
    /**
     * Defines a test email
     *
     * @param string $recipient Recipient's email address
     * @return Email
     */
    public function test($recipient)
    {
        return $this
            ->setTo($recipient)
            ->setSubject('CRI: Test email')
            ->setTemplate('test');
    }
}
