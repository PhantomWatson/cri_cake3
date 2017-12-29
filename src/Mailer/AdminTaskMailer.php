<?php
namespace App\Mailer;

use App\Model\Table\ProductsTable;
use Cake\Mailer\Email;
use Cake\Mailer\Mailer;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;
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
    public function deliverMandatoryPresentation($data)
    {
        return $this
            ->setStandardConfig($data)
            ->setTemplate('task_deliver_presentation')
            ->setViewVars([
                'actionUrl' => $this->getTaskUrl([
                    'controller' => 'Deliveries',
                    'action' => 'add'
                ]),
                'presentationLetter' => $this->getDeliverablePresentationLetter([
                    'surveyType' => $data['meta']['surveyType']
                ]),
                'surveyType' => $data['surveyType']
            ]);
    }

    /**
     * Defines an email informing an administrator that presentation B or D needs to be delivered
     *
     * @param array $data Metadata
     * @return Email
     * @throws InternalErrorException
     */
    public function deliverOptionalPresentation($data)
    {
        return $this
            ->setStandardConfig($data)
            ->setTemplate('task_deliver_optional_presentation')
            ->setViewVars([
                'actionUrl' => $this->getTaskUrl([
                    'controller' => 'Deliveries',
                    'action' => 'add'
                ]),
                'presentationLetter' => $this->getDeliverablePresentationLetter([
                    'productId' => $data['meta']['productId']
                ])
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
     * Returns the mandatory presentation letter associated with the specified survey type
     *
     * @param array $params An array containing either the key 'surveyType' or 'productId'
     * @return string
     * @throws InternalErrorException
     */
    private function getDeliverablePresentationLetter($params)
    {
        if (isset($params['surveyType'])) {
            switch ($params['surveyType']) {
                case 'official':
                    return 'A';
                case 'organization':
                    return 'C';
                default:
                    throw new InternalErrorException('Unrecognized survey type: ' . $params['surveyType']);
            }
        }

        if (isset($params['productId'])) {
            /** @var ProductsTable $productsTable */
            $productsTable = TableRegistry::get('Products');
            $letter = $productsTable->getPresentationLetter($params['productId']);

            return $letter ? strtoupper($letter) : null;
        }

        throw new InternalErrorException('No valid param provided to getDeliverablePresentationLetter()');
    }
}
