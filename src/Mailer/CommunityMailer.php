<?php
namespace App\Mailer;

use Cake\Mailer\Email;
use Cake\Mailer\Mailer;
use Cake\Routing\Router;

class CommunityMailer extends Mailer
{
    /**
     * Defines an email informing a user that their community has been advanced to the next step
     *
     * @param array $user User data
     * @param array $community Community data
     * @param int $toStep The step number the community has just been promoted to
     * @return Email
     */
    public function communityPromoted($user, $community, $toStep)
    {
        return $this
            ->setTo($user['email'])
            ->setSubject('Community Readiness Initiative - Your community has advanced to Step ' . $toStep)
            ->setViewVars([
                'communityName' => $community['name'],
                'homeUrl' => Router::url('/', true),
                'toStep' => $toStep,
                'userName' => $user['name']
            ])
            ->setTemplate('community_promoted')
            ->setDomain('cri.cberdata.org');
    }
}
