<?php
namespace App\Shell;

use App\Alerts\Alertable;
use App\Alerts\AlertRecipients;
use Cake\Console\Shell;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
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

        $alertableCommunities = [];
        foreach ($communities as $communityId => $communityName) {
            $alertable = new Alertable($communityId);
            foreach ($alertableMethods as $alertableMethod) {
                if ($alertable->{$alertableMethod->name}()) {
                    $alertableCommunities[$communityName][] = $alertableMethod->name;
                }
            }
        }

        if (empty($alertableCommunities)) {
            $this->out('No alert conditions met for any communities');
        } else {
            $msg = 'Alert conditions met by ' .
                count($alertableCommunities) .
                __n(' community', ' communities', count($alertableCommunities)) .
                "\n";
            $this->out($msg);

            $alertRecipientCounts = [];
            $alertRecipients = new AlertRecipients();

            foreach ($alertableCommunities as $communityName => $alertables) {
                $this->success("$communityName:");
                foreach ($alertables as $alertable) {
                    // Remember recipient count for each alert type
                    if (array_key_exists($alertable, $alertRecipientCounts)) {
                        $recipientCount = $alertRecipientCounts[$alertable];
                    } else {
                        $recipientCount = $alertRecipients->getRecipientCount($alertable);
                        $recipientCount .= __n(' recipient', ' recipients', $recipientCount);
                        $alertRecipientCounts[$alertable] = $recipientCount;
                    }

                    $alertableNice = Inflector::humanize(Inflector::underscore($alertable));
                    $this->out(" - $alertableNice ($recipientCount)");
                }
            }
        }
    }
}
