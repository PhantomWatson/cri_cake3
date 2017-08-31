<?php
namespace App\Mailer\Preview;

use Cake\Mailer\Email;
use DebugKit\Mailer\MailPreview;

class CommunityEmailPreview extends MailPreview
{
    /**
     * Preview method for CommunityMailer::communityPromoted()
     *
     * @return Email
     */
    public function communityPromoted()
    {
        $user = [
            'name' => 'Client Name',
            'email' => 'client@example.com'
        ];
        $community = [
            'name' => 'Community Name'
        ];
        $toStep = 3;

        return $this->getMailer('Community')
            ->communityPromoted($user, $community, $toStep);
    }
}
