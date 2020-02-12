<?php
declare(strict_types=1);

namespace App\Mailer\Preview;

use DebugKit\Mailer\MailPreview;

class CommunityEmailPreview extends MailPreview
{
    /**
     * Preview method for CommunityMailer::communityPromoted()
     *
     * @return \Cake\Mailer\Email
     */
    public function communityPromoted()
    {
        $user = [
            'name' => 'Client Name',
            'email' => 'client@example.com',
        ];
        $community = [
            'name' => 'Community Name',
        ];
        $toStep = 3;

        return $this->getMailer('Community')
            ->communityPromoted($user, $community, $toStep);
    }
}
