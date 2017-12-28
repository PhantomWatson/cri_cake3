<?php
namespace App\Mailer;

use Cake\Mailer\Email;
use Cake\Mailer\Mailer;
use Cake\Network\Exception\InternalErrorException;
use Cake\Routing\Router;

class AdminTaskMailer extends Mailer
{
    /**
     * Defines an email informing an administrator that a presentation needs to be delivered
     *
     * @param array $data Metadata
     * @return Email
     * @throws InternalErrorException
     */
    public function deliverPresentation($data)
    {
        $user = $data['user'];
        $community = $data['community'];
        $presentationLetter = $this->getDeliverablePresentationLetter($data['meta']['surveyType']);

        // Workaround for this bug: https://github.com/cakephp/cakephp/issues/11582
        $actionUrl = Router::url([
            'plugin' => false,
            'prefix' => 'admin',
            'controller' => 'Deliveries',
            'action' => 'add',
            '_full' => true
        ]);
        $actionUrl = str_replace('http://', 'https://', $actionUrl);

        return $this
            ->setTo($user['email'])
            ->setSubject('Community Readiness Initiative - Action required')
            ->setDomain('cri.cberdata.org')
            ->setTemplate('task_deliver_presentation')
            ->setViewVars([
                'actionUrl' => $actionUrl,
                'communityName' => $community['name'],
                'homeUrl' => Router::url('/', true),
                'presentationLetter' => $presentationLetter,
                'surveyType' => $data['surveyType'],
                'userName' => $user['name']
            ]);
    }

    /**
     * Returns the mandatory presentation letter associated with the specified survey type
     *
     * @param string $surveyType Survey type
     * @return string
     */
    private function getDeliverablePresentationLetter($surveyType)
    {
        switch ($surveyType) {
            case 'official':
                return 'A';
            case 'organization':
                return 'C';
            default:
                throw new InternalErrorException('Unrecognized survey type: ' . $surveyType);
        }
    }
}
