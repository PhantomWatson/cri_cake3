<?php
namespace App\Alerts;

use App\Model\Entity\Community;
use App\Model\Entity\User;
use App\Model\Table\UsersTable;
use Cake\ORM\TableRegistry;
use DateTime;
use Queue\Model\Table\QueuedJobsTable;
use ReflectionClass;

class Alert
{
    /**
     * Returns an array with each key being an alertable community name and each value being an array of applicable
     * alert method names
     *
     * @return array
     */
    public static function getAlertableCommunities()
    {
        // Get active communities
        $communitiesTable = TableRegistry::get('Communities');
        $communities = $communitiesTable
            ->find('list')
            ->where(['active' => true])
            ->orderAsc('name');

        // Get list of Alertable methods
        $class = new ReflectionClass('App\Alerts\Alertable');
        $alertableMethods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Remove __construct() from list of methods
        array_shift($alertableMethods);

        $alertableCommunities = [];
        foreach ($communities as $communityId => $communityName) {
            $alertable = new Alertable($communityId);
            $applicableAlerts = [];
            foreach ($alertableMethods as $alertableMethod) {
                if ($alertable->{$alertableMethod->name}()) {
                    $applicableAlerts[] = $alertableMethod->name;
                }
            }
            if ($applicableAlerts) {
                $alertableCommunities[] = [
                    'id' => $communityId,
                    'name' => $communityName,
                    'alerts' => $applicableAlerts
                ];
            }
        }

        return $alertableCommunities;
    }
}
