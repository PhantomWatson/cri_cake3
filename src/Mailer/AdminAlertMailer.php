<?php
declare(strict_types=1);

namespace App\Mailer;

use Cake\Mailer\Email;
use Cake\Mailer\Mailer;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

/**
 * Class AdminAlertMailer
 * @package App\Mailer
 * @property \App\Model\Table\CommunitiesTable $communities
 */
class AdminAlertMailer extends Mailer
{
    private $communities;

    /**
     * AdminAlertMailer constructor
     *
     * @param \Cake\Mailer\Email|null $email Email object or null
     */
    public function __construct(?Email $email = null)
    {
        parent::__construct($email);
        $this->communities = TableRegistry::get('Communities');
    }

    /**
     * Defines a "deliver presentation A" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    public function deliverPresentationA($data)
    {
        $data['presentationLetter'] = 'a';

        return $this->deliverPresentation($data);
    }

    /**
     * Defines a "deliver presentation C" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    public function deliverPresentationC($data)
    {
        $data['presentationLetter'] = 'c';

        return $this->deliverPresentation($data);
    }

    /**
     * Defines a "deliver presentation B" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    public function deliverPresentationB($data)
    {
        $data['presentationLetter'] = 'b';

        return $this->deliverPresentation($data);
    }

    /**
     * Defines a "deliver presentation D" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    public function deliverPresentationD($data)
    {
        $data['presentationLetter'] = 'd';

        return $this->deliverPresentation($data);
    }

    /**
     * Defines a "deliver mandatory presentation" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     * @throws \Cake\Network\Exception\InternalErrorException
     */
    private function deliverPresentation($data)
    {
        return $this
            ->setStandardConfig($data)
            ->setTemplate('admin_alert/deliver_presentation')
            ->setViewVars([
                'actionUrl' => $this->getTaskUrl([
                    'controller' => 'Deliveries',
                    'action' => 'add',
                ]),
                'presentationLetter' => $data['presentationLetter'],
            ]);
    }

    /**
     * Sets mailer configuration shared by multiple methods in this class
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    private function setStandardConfig($data)
    {
        return $this
            ->setTo($data['user']['email'])
            ->setSubject('Community Readiness Initiative - Action required for ' . $data['community']['name'])
            ->setDomain('cri.cberdata.org')
            ->setViewVars([
                'communityName' => $data['community']['name'],
                'userName' => $data['user']['name'],
            ]);
    }

    /**
     * Returns a URL corresponding to an admin task
     *
     * In addition to being shorthand for a full call to Router::url(), this implements a workaround for this bug:
     * https://github.com/cakephp/cakephp/issues/11582
     *
     * @param array $url URL array
     * @return string
     */
    private function getTaskUrl($url)
    {
        $url = $url + [
            'plugin' => false,
            'prefix' => 'admin',
            '_full' => true,
        ];

        return str_replace('http://', 'https://', Router::url($url));
    }

    /**
     * Defines a "create officials survey" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    public function createOfficialsSurvey($data)
    {
        $data['surveyType'] = 'official';

        return $this->createSurvey($data);
    }

    /**
     * Defines a "create organizations survey" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    public function createOrganizationsSurvey($data)
    {
        $data['surveyType'] = 'organization';

        return $this->createSurvey($data);
    }

    /**
     * Defines an email informing an administrator that it's time to create a survey
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    private function createSurvey($data)
    {
        $slug = $this->communities->get($data['community']['id'])->slug;

        return $this
            ->setStandardConfig($data)
            ->setTemplate('admin_alert/create_survey')
            ->setViewVars([
                'actionUrl' => $this->getTaskUrl([
                    'controller' => 'Surveys',
                    'action' => 'link',
                    $slug,
                    $data['surveyType'],
                ]),
                'surveyType' => $data['surveyType'],
            ]);
    }

    /**
     * Defines a "create clients" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    public function createClients($data)
    {
        return $this
            ->setStandardConfig($data)
            ->setTemplate('admin_alert/create_clients')
            ->setViewVars([
                'actionUrl' => $this->getTaskUrl([
                    'controller' => 'Communities',
                    'action' => 'addClient',
                    $data['community']['id'],
                ]),
            ]);
    }

    /**
     * Defines an "activate officials survey" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    public function activateOfficialsSurvey($data)
    {
        $data['surveyType'] = 'official';

        return $this->activateSurvey($data);
    }

    /**
     * Defines an "activate organizations survey" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    public function activateOrganizationsSurvey($data)
    {
        $data['surveyType'] = 'organization';

        return $this->activateSurvey($data);
    }

    /**
     * Defines an "activate survey" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    private function activateSurvey($data)
    {
        return $this
            ->setStandardConfig($data)
            ->setTemplate('admin_alert/activate_survey')
            ->setViewVars([
                'actionUrl' => $this->getTaskUrl([
                    'controller' => 'Surveys',
                    'action' => 'activate',
                    $data['surveyType'],
                ]),
                'surveyType' => $data['surveyType'],
            ]);
    }

    /**
     * Defines a "schedule presentation A" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    public function schedulePresentationA($data)
    {
        $data['presentationLetter'] = 'a';

        return $this->schedulePresentation($data);
    }

    /**
     * Defines a "schedule presentation B" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    public function schedulePresentationB($data)
    {
        $data['presentationLetter'] = 'b';

        return $this->schedulePresentation($data);
    }

    /**
     * Defines a "schedule presentation C" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    public function schedulePresentationC($data)
    {
        $data['presentationLetter'] = 'c';

        return $this->schedulePresentation($data);
    }

    /**
     * Defines a "schedule presentation D" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    public function schedulePresentationD($data)
    {
        $data['presentationLetter'] = 'd';

        return $this->schedulePresentation($data);
    }

    /**
     * Defines a "schedule presentation" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    private function schedulePresentation($data)
    {
        $slug = $this->communities->get($data['community']['id'])->slug;

        return $this
            ->setStandardConfig($data)
            ->setTemplate('admin_alert/schedule_presentation')
            ->setViewVars([
                'actionUrl' => $this->getTaskUrl([
                    'controller' => 'Communities',
                    'action' => 'presentations',
                    $slug,
                ]),
                'presentationLetter' => $data['presentationLetter'],
            ]);
    }

    /**
     * Defines a "delivery policy development materials" email
     *
     * @param array $data Metadata
     * @return \Cake\Mailer\Email
     */
    public function deliverPolicyDev($data)
    {
        $clients = $this->communities->getClients($data['community']['id']);

        return $this
            ->setStandardConfig($data)
            ->setTemplate('admin_alert/deliver_policy_dev')
            ->setViewVars([
                'actionUrl' => $this->getTaskUrl([
                    'controller' => 'Deliveries',
                    'action' => 'add',
                ]),
                'clients' => $clients,
            ]);
    }
}
