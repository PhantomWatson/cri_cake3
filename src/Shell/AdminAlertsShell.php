<?php
namespace App\Shell;

use App\Alerts\Alert;
use App\Alerts\AlertRecipients;
use Cake\Console\Shell;
use Cake\Utility\Inflector;

class AdminAlertsShell extends Shell
{
    private $alertRecipientCounts = [];

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
        $alertableCommunities = Alert::getAlertableCommunities();

        if (empty($alertableCommunities)) {
            $this->out('No alert conditions met for any communities');
        } else {
            $msg = 'Alert conditions met by ' .
                count($alertableCommunities) .
                __n(' community', ' communities', count($alertableCommunities)) .
                "\n";
            $this->out($msg);

            foreach ($alertableCommunities as $community) {
                $this->success($community['name'] . ':');
                foreach ($community['alerts'] as $alertable) {
                    $recipientCount = $this->getRecipientCount($alertable);
                    $alertableNice = Inflector::humanize(Inflector::underscore($alertable));
                    $this->out(" - $alertableNice ($recipientCount)");
                }
            }
        }
    }

    /**
     * Returns the number of recipients who would receive the specified alert
     *
     * @param string $alertMethodName Such as createClients or deliverPolicyDev
     * @return int
     */
    private function getRecipientCount($alertMethodName)
    {
        $alertRecipients = new AlertRecipients();
        if (array_key_exists($alertMethodName, $this->alertRecipientCounts)) {
            return $this->alertRecipientCounts[$alertMethodName];
        }

        $recipientCount = $alertRecipients->getRecipientCount($alertMethodName);
        $recipientCount .= __n(' recipient', ' recipients', $recipientCount);
        $this->alertRecipientCounts[$alertMethodName] = $recipientCount;

        return $recipientCount;
    }
}
