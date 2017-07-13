<?php
namespace App\Mailer\Preview;

use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use DebugKit\Mailer\MailPreview;

class UserEmailPreview extends MailPreview
{
    /**
     * Preview method for UserMailer::newAccount()
     *
     * @return Email
     */
    public function newAccount()
    {
        $usersTable = TableRegistry::get('Users');
        $user = $usersTable->find()->first();

        return $this->getMailer('User')
            ->newAccount($user, 'fake-password');
    }

    /**
     * Preview method for UserMailer::resetPassword()
     *
     * @return Email
     */
    public function resetPassword()
    {
        $userId = 1;

        return $this->getMailer('User')
            ->resetPassword($userId);
    }
}
