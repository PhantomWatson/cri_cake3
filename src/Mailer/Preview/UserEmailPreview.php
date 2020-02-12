<?php
declare(strict_types=1);

namespace App\Mailer\Preview;

use Cake\ORM\TableRegistry;
use DebugKit\Mailer\MailPreview;

class UserEmailPreview extends MailPreview
{
    /**
     * Preview method for UserMailer::newAccount()
     *
     * @return \Cake\Mailer\Email
     */
    public function newAccount()
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->find()->first();

        return $this->getMailer('User')
            ->newAccount($user, 'fake-password');
    }

    /**
     * Preview method for UserMailer::resetPassword()
     *
     * @return \Cake\Mailer\Email
     */
    public function resetPassword()
    {
        $userId = 1;

        return $this->getMailer('User')
            ->resetPassword($userId);
    }
}
