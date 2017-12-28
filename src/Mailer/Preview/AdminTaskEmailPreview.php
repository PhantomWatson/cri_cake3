<?php
namespace App\Mailer\Preview;

use App\Mailer\AdminTaskMailer;
use Cake\Mailer\Email;
use DebugKit\Mailer\MailPreview;

class AdminTaskEmailPreview extends MailPreview
{
    /**
     * Preview method for CommunityMailer::deliverMandatoryPresentation()
     *
     * @return Email
     */
    public function deliverMandatoryPresentation()
    {
        $data = [
            'user' => [
                'name' => 'Recipient Name',
                'email' => 'recipient@example.com'
            ],
            'community' => [
                'name' => 'Community Name'
            ],
            'surveyType' => 'official'
        ];

        /** @var AdminTaskMailer $mailer */
        $mailer = $this->getMailer('AdminTask');

        return $mailer->deliverMandatoryPresentation($data);
    }
}
