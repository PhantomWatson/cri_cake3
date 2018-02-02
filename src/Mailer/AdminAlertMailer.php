<?php
namespace App\Mailer;

use App\Model\Table\CommunitiesTable;
use Cake\Mailer\Email;
use Cake\Mailer\Mailer;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

/**
 * Class AdminAlertMailer
 * @package App\Mailer
 * @property CommunitiesTable $communities
 */
class AdminAlertMailer extends Mailer
{
    private $communities;

    /**
     * AdminAlertMailer constructor
     *
     * @param Email|null $email Email object or null
     */
    public function __construct(Email $email = null)
    {
        parent::__construct($email);
        $this->communities = TableRegistry::get('Communities');
    }

    /**
     * Defines a "deliver presentation A" email
     *
     * @param array $data Metadata
     * @return Email
     */
    public function deliverPresentationA($data)
    {
        $data['presentationLetter'] = 'a';
        $data['surveyType'] = 'official';

        return $this->deliverMandatoryPresentation($data);
    }

    /**
     * Defines a "deliver presentation C" email
     *
     * @param array $data Metadata
     * @return Email
     */
    public function deliverPresentationC($data)
    {
        $data['presentationLetter'] = 'c';
        $data['surveyType'] = 'organization';

        return $this->deliverMandatoryPresentation($data);
    }

    /**
     * Defines a "deliver mandatory presentation" email
     *
     * @param array $data Metadata
     * @return Email
     * @throws InternalErrorException
     */
    private function deliverMandatoryPresentation($data)
    {
        return $this
            ->setStandardConfig($data)
            ->setTemplate('task_deliver_presentation')
            ->setViewVars([
                'actionUrl' => $this->getTaskUrl([
                    'controller' => 'Deliveries',
                    'action' => 'add'
                ]),
                'presentationLetter' => $data['presentationLetter'],
                'surveyType' => $data['surveyType']
            ]);
    }

    /**
     * Defines a "deliver presentation B" email
     *
     * @param array $data Metadata
     * @return Email
     */
    public function deliverPresentationB($data)
    {
        $data['presentationLetter'] = 'b';

        return $this->deliverOptionalPresentation($data);
    }

    /**
     * Defines a "deliver presentation D" email
     *
     * @param array $data Metadata
     * @return Email
     */
    public function deliverPresentationD($data)
    {
        $data['presentationLetter'] = 'd';

        return $this->deliverOptionalPresentation($data);
    }

    /**
     * Defines an email informing an administrator that presentation B or D needs to be delivered
     *
     * @param array $data Metadata
     * @return Email
     * @throws InternalErrorException
     */
    private function deliverOptionalPresentation($data)
    {
        return $this
            ->setStandardConfig($data)
            ->setTemplate('task_deliver_optional_presentation')
            ->setViewVars([
                'actionUrl' => $this->getTaskUrl([
                    'controller' => 'Deliveries',
                    'action' => 'add'
                ]),
                'presentationLetter' => $data['presentationLetter']
            ]);
    }

    /**
     * Sets mailer configuration shared by multiple methods in this class
     *
     * @param array $data Metadata
     * @return Email
     */
    private function setStandardConfig($data)
    {
        return $this
            ->setTo($data['user']['email'])
            ->setSubject('Community Readiness Initiative - Action required')
            ->setDomain('cri.cberdata.org')
            ->setViewVars([
                'communityName' => $data['community']['name'],
                'userName' => $data['user']['name']
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
            '_full' => true
        ];

        return str_replace('http://', 'https://', Router::url($url));
    }

    /**
     * Defines a "create officials survey" email
     *
     * @param array $data Metadata
     * @return Email
     */
    public function createOfficialsSurvey($data)
    {
        $data['community']['slug'] = $this->communities->get($data['community']['id'])->slug;
        $data['surveyType'] = 'official';

        return $this->createSurvey($data);
    }

    /**
     * Defines a "create organizations survey" email
     *
     * @param array $data Metadata
     * @return Email
     */
    public function createOrgsSurvey($data)
    {
        $data['community']['slug'] = $this->communities->get($data['community']['id'])->slug;
        $data['surveyType'] = 'organization';

        return $this->createSurvey($data);
    }

    /**
     * Defines an email informing an administrator that it's time to create a survey
     *
     * @param array $data Metadata
     * @return Email
     */
    private function createSurvey($data)
    {
        return $this
            ->setStandardConfig($data)
            ->setTemplate('task_create_survey')
            ->setViewVars([
                'actionUrl' => $this->getTaskUrl([
                    'controller' => 'Surveys',
                    'action' => 'link',
                    $data['community']['slug'],
                    $data['surveyType']
                ])
            ]);
    }

    /**
     * Defines a "create clients" email
     *
     * @param array $data Metadata
     * @return Email
     */
    public function createClients($data)
    {
        return $this
            ->setStandardConfig($data)
            ->setTemplate('task_assign_client')
            ->setViewVars([
                'actionUrl' => $this->getTaskUrl([
                    'controller' => 'Communities',
                    'action' => 'addClient',
                    $data['community']['id']
                ])
            ]);
    }
}
