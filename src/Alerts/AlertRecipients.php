<?php
declare(strict_types=1);

namespace App\Alerts;

use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;

class AlertRecipients
{
    /**
     * Returns the name of the user group (CBER, ICI, or both) that receives alerts of the specified type
     *
     * @param string $alertMethodName Alert method name
     * @return string
     */
    public function getUserGroup($alertMethodName)
    {
        $adminGroupsMap = [
            'deliverPresentationA' => 'CBER',
            'deliverPresentationB' => 'CBER',
            'deliverPresentationC' => 'CBER',
            'deliverPresentationD' => 'CBER',
            'createOfficialsSurvey' => 'ICI',
            'createOrganizationsSurvey' => 'ICI',
            'createClients' => 'ICI',
            'activateOfficialsSurvey' => 'ICI',
            'activateOrganizationsSurvey' => 'ICI',
            'schedulePresentationA' => 'ICI',
            'schedulePresentationB' => 'ICI',
            'schedulePresentationC' => 'ICI',
            'schedulePresentationD' => 'ICI',
            'deliverPolicyDev' => 'both',
        ];

        if (!array_key_exists($alertMethodName, $adminGroupsMap)) {
            throw new InternalErrorException("Alert method $alertMethodName not recognized");
        }

        return $adminGroupsMap[$alertMethodName];
    }

    /**
     * Returns the count of users who would receive an alert of the specified type
     *
     * @param string $alertMethodName Alert method name (e.g. deliverPresentationA)
     * @return int
     */
    public function getRecipientCount($alertMethodName)
    {
        $adminGroup = $this->getUserGroup($alertMethodName);

        return $this->findRecipients($adminGroup)->count();
    }

    /**
     * Returns an array of users who should receive an alert of the specified type
     *
     * @param string $alertMethodName Alert method name (e.g. deliverPresentationA)
     * @return \App\Model\Entity\User[]
     */
    public function getRecipients($alertMethodName)
    {
        $adminGroup = $this->getUserGroup($alertMethodName);

        return $this->findRecipients($adminGroup)->all();
    }

    /**
     * Returns a Query for finding users subscribed to the specified mailing list
     *
     * @param string $adminGroup Either 'CBER', 'ICI', or 'both'
     * @return \Cake\ORM\Query
     * @throws \Cake\Network\Exception\InternalErrorException
     */
    public function findRecipients($adminGroup)
    {
        $usersTable = TableRegistry::get('Users');

        if ($adminGroup == 'ICI') {
            return $usersTable->find()->where(['ici_email_optin' => true]);
        }

        if ($adminGroup == 'CBER') {
            return $usersTable->find()->where(['cber_email_optin' => true]);
        }

        if ($adminGroup == 'both') {
            return $usersTable->find()->where([
                'OR' => [
                    'cber_email_optin' => true,
                    'ici_email_optin' => true,
                ],
            ]);
        }

        throw new InternalErrorException("Admin group $adminGroup not recognized");
    }
}
