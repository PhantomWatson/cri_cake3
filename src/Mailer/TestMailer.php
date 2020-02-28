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
     * @return \Cake\Mailer\Mailer
     */
    public function test($recipient)
    {
        $email = $this
            ->setTo($recipient)
            ->setSubject('CRI: Test email')
            ->setDomain('cri.cberdata.org');

        $email->viewBuilder()->setTemplate('test');

        return $email;
    }
}
