<?php
declare(strict_types=1);

namespace App\Mailer;

use Cake\Mailer\Mailer;

class TestMailer extends Mailer
{
    /**
     * Defines a test email
     *
     * @param string $recipient Recipient's email address
     * @return Mailer
     */
    public function test($recipient)
    {
        return $this
            ->setTo($recipient)
            ->setSubject('CRI: Test email')
            ->setTemplate('test')
            ->setDomain('cri.cberdata.org');
    }
}
