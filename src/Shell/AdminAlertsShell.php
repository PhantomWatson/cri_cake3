<?php
namespace App\Shell;

use App\Alerts\Alertable;
use Cake\Console\Shell;
use Cake\ORM\TableRegistry;
use ReflectionClass;

class AdminAlertsShell extends Shell
{
    /**
     * Display help for this console.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addSubcommand('status', [
            'help' => 'Shows what alerts are currently valid and who would receive those alerts',
        ]);
        $parser->addSubcommand('run', [
            'help' => 'Sends any currently valid alerts',
        ]);

        return $parser;
    }

    /**
     * Shows what alerts are currently valid and who would receive those alerts
     *
     * @return void
     */
    public function status()
    {
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

        foreach ($communities as $communityId => $communityName) {
            $alertable = new Alertable($communityId);
            $positiveAlertables = [];
            foreach ($alertableMethods as $alertableMethod) {
                if ($alertable->{$alertableMethod->name}()) {
                    $positiveAlertables[] = $alertableMethod->name;
                }
            }
            if (empty($positiveAlertables)) {
                $this->out("$communityName: none");
            } else {
                $this->success("$communityName:");
                foreach ($positiveAlertables as $positiveAlertable) {
                    $this->success(" - $positiveAlertable");
                }
            }
        }
    }
}
