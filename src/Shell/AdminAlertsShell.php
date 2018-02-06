<?php
namespace App\Shell;

use App\Alerts\Alert;
use App\Alerts\AlertRecipients;
use App\Alerts\AlertSender;
use App\Mailer\AdminAlertMailer;
use App\Model\Entity\User;
use Cake\Console\Shell;
use Cake\I18n\Time;
use Cake\Utility\Inflector;

class AdminAlertsShell extends Shell
{
    private $alertRecipientCounts = [];
    private $alertRecipients = [];

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

            $mailer = new AdminAlertMailer();
            foreach ($alertableCommunities as $community) {
                $this->info($community['name'] . ':');
                foreach ($community['alerts'] as $alertName) {
                    $alertableNice = Inflector::humanize(Inflector::underscore($alertName));
                    if (method_exists($mailer, $alertName)) {
                        $recipientCount = $this->getRecipientCount($alertName);
                        $this->out("  - $alertableNice ($recipientCount)");
                    } else {
                        $this->err("  - $alertableNice alert not available");
                    }
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

    /**
     * Returns the recipients who would receive the specified alert
     *
     * @param string $alertName Such as createClients or deliverPolicyDev
     * @return User[]
     */
    private function getRecipients($alertName)
    {
        $alertRecipients = new AlertRecipients();
        $adminGroup = $alertRecipients->getUserGroup($alertName);
        if (array_key_exists($adminGroup, $this->alertRecipients)) {
            return $this->alertRecipients[$adminGroup];
        }

        $recipients = $alertRecipients->getRecipients($alertName);
        $this->alertRecipients[$adminGroup] = $recipients;

        return $recipients;
    }

    /**
     * Sends alerts
     *
     * @return void
     * @throws \Exception
     */
    public function run()
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
                $this->info($community['name'] . ':');
                $alertSender = new AlertSender($community['id']);
                foreach ($community['alerts'] as $alertName) {
                    $this->sendAlert($alertSender, $alertName);
                }
            }
        }
    }

    /**
     * Attempts to send an alert, outputting the results
     *
     * @param AlertSender $alertSender AlertSender object
     * @param string $alertName Such as createClients or deliverPolicyDev
     * @return void
     * @throws \Exception
     */
    private function sendAlert($alertSender, $alertName)
    {
        $alertableNice = Inflector::humanize(Inflector::underscore($alertName));
        if (!method_exists(new AdminAlertMailer(), $alertName)) {
            $this->err("  - $alertableNice alert not available");

            return;
        }

        $this->out("  - Sending $alertableNice alert...");
        $recipients = $this->getRecipients($alertName);
        foreach ($recipients as $recipient) {
            $recentlySent = $alertSender->isRecentlySent($recipient->email, $alertName);
            if ($recentlySent) {
                $timeAgo = (new Time($recentlySent))->timeAgoInWords();
                $this->out("    - {$recipient->email} skipped (sent $timeAgo)");
                continue;
            }

            if ($alertSender->enqueueEmail($recipient, $alertName)) {
                $this->out("    - Alert to {$recipient->email} enqueued");
                continue;
            }

            $this->err("    - Alert to {$recipient->email} could not be enqueued");
        }
    }
}
