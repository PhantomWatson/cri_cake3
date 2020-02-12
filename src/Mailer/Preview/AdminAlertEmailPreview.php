<?php
declare(strict_types=1);

namespace App\Mailer\Preview;

use Cake\ORM\TableRegistry;
use DebugKit\Mailer\MailPreview;

/**
 * Class AdminAlertEmailPreview
 * @package App\Mailer\Preview
 * @property \App\Mailer\AdminAlertMailer $mailer
 */
class AdminAlertEmailPreview extends MailPreview
{
    private $mailer;
    private $data = [];

    /**
     * AdminAlertEmailPreview constructor
     */
    public function __construct()
    {
        // Get an arbitrary existing community ID so AdminAlertMailer's calls to CommunitiesTable::get() don't fail
        $communities = TableRegistry::getTableLocator()->get('Communities');
        $communityId = $communities->find()->select(['id'])->first()->id;

        $this->mailer = $this->getMailer('AdminAlert');
        $this->data = [
            'user' => [
                'name' => 'Recipient Name',
                'email' => 'recipient@example.com',
            ],
            'community' => [
                'id' => $communityId,
                'name' => 'Community Name',
                'slug' => 'community-name',
            ],
        ];
    }

    /**
     * Preview method for AdminAlertMailer::deliverPresentationA()
     *
     * @return \Cake\Mailer\Email
     */
    public function deliverPresentationA()
    {
        return $this->mailer->deliverPresentationA($this->data);
    }

    /**
     * Preview method for AdminAlertMailer::()
     *
     * @return \Cake\Mailer\Email
     */
    public function deliverPresentationB()
    {
        return $this->mailer->deliverPresentationB($this->data);
    }

    /**
     * Preview method for AdminAlertMailer::()
     *
     * @return \Cake\Mailer\Email
     */
    public function deliverPresentationC()
    {
        return $this->mailer->deliverPresentationC($this->data);
    }

    /**
     * Preview method for AdminAlertMailer::()
     *
     * @return \Cake\Mailer\Email
     */
    public function deliverPresentationD()
    {
        return $this->mailer->deliverPresentationD($this->data);
    }

    /**
     * Preview method for AdminAlertMailer::()
     *
     * @return \Cake\Mailer\Email
     */
    public function createOfficialsSurvey()
    {
        return $this->mailer->createOfficialsSurvey($this->data);
    }

    /**
     * Preview method for AdminAlertMailer::()
     *
     * @return \Cake\Mailer\Email
     */
    public function createOrganizationsSurvey()
    {
        return $this->mailer->createOrganizationsSurvey($this->data);
    }

    /**
     * Preview method for AdminAlertMailer::()
     *
     * @return \Cake\Mailer\Email
     */
    public function createClients()
    {
        return $this->mailer->createClients($this->data);
    }

    /**
     * Preview method for AdminAlertMailer::()
     *
     * @return \Cake\Mailer\Email
     */
    public function activateOfficialsSurvey()
    {
        return $this->mailer->activateOfficialsSurvey($this->data);
    }

    /**
     * Preview method for AdminAlertMailer::()
     *
     * @return \Cake\Mailer\Email
     */
    public function activateOrganizationsSurvey()
    {
        return $this->mailer->activateOrganizationsSurvey($this->data);
    }

    /**
     * Preview method for AdminAlertMailer::()
     *
     * @return \Cake\Mailer\Email
     */
    public function schedulePresentationA()
    {
        return $this->mailer->schedulePresentationA($this->data);
    }

    /**
     * Preview method for AdminAlertMailer::()
     *
     * @return \Cake\Mailer\Email
     */
    public function schedulePresentationB()
    {
        return $this->mailer->schedulePresentationB($this->data);
    }

    /**
     * Preview method for AdminAlertMailer::()
     *
     * @return \Cake\Mailer\Email
     */
    public function schedulePresentationC()
    {
        return $this->mailer->schedulePresentationC($this->data);
    }

    /**
     * Preview method for AdminAlertMailer::()
     *
     * @return \Cake\Mailer\Email
     */
    public function schedulePresentationD()
    {
        return $this->mailer->schedulePresentationD($this->data);
    }

    /**
     * Preview method for AdminAlertMailer::()
     *
     * @return \Cake\Mailer\Email
     */
    public function deliverPolicyDev()
    {
        return $this->mailer->deliverPolicyDev($this->data);
    }
}
