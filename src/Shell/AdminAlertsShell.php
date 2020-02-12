<?php
declare(strict_types=1);

namespace App\Shell;

use App\Alerts\Alertable;
use App\Alerts\AlertRecipients;
use App\Alerts\AlertSender;
use App\Mailer\AdminAlertMailer;
use Cake\Console\Shell;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use ReflectionClass;

class AdminAlertsShell extends Shell
{
    private $alertRecipientCounts = [];
    private $alertRecipients = [];
    private $includeDummy = false;

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
        $parser->addSubcommand('subscribers', [
            'help' => 'Shows subscribers to administrator alerts',
        ]);
        $parser->addOption('dummy', [
            'short' => 'd',
            'boolean' => true,
            'help' => 'Include dummy communities',
            'default' => false,
        ]);

        return $parser;
    }

    /**
     * Shows what alerts are currently valid and who would receive those alerts
     *
     * @return void
     * @throws \ReflectionException
     */
    public function status()
    {
        $this->includeDummy = $this->params['dummy'];
        $alertableCommunities = $this->getAlertableCommunities();

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
     * @return \App\Model\Entity\User[]
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
        $this->includeDummy = $this->params['dummy'];
        $alertableCommunities = $this->getAlertableCommunities();

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
     * @param \App\Alerts\AlertSender $alertSender AlertSender object
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

    /**
     * Returns an array with each key being an alertable community name and each value being an array of applicable
     * alert method names
     *
     * @return array
     * @throws \ReflectionException
     */
    private function getAlertableCommunities()
    {
        // Get active communities
        $communitiesTable = TableRegistry::getTableLocator()->get('Communities');
        $conditions = ['active' => true];
        if (!$this->includeDummy) {
            $conditions['dummy'] = false;
        }
        $communities = $communitiesTable
            ->find('list')
            ->where($conditions)
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
                    'alerts' => $applicableAlerts,
                ];
            }
        }

        return $alertableCommunities;
    }

    /**
     * Shows subscribers to administrator alerts
     *
     * @return void
     */
    public function subscribers()
    {
        $subscribers = [];
        $subscribers['only ICI'] = $this->getSubscribers(true, false);
        $subscribers['only CBER'] = $this->getSubscribers(false, true);
        $subscribers['both'] = $this->getSubscribers(true, true);
        $subscribers['neither'] = $this->getSubscribers(false, false);

        $this->out("\nListing all CRI administrators...");
        foreach ($subscribers as $group => $groupedSubscribers) {
            $this->info("\nSubscribed to $group alerts:");
            if ($groupedSubscribers->isEmpty()) {
                $this->out(' - (none)');
                continue;
            }
            foreach ($groupedSubscribers as $subscriber) {
                $this->out(" - $subscriber->name ($subscriber->email)");
            }
        }
    }

    /**
     * @param bool $ici True if selecting users subscribed to ICI alert emails
     * @param bool $cber True if selecting users subscribed to CBER alert emails
     *
     * @return \Cake\Datasource\ResultSetInterface
     */
    private function getSubscribers($ici, $cber)
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');

        return $usersTable->find()
            ->select(['name', 'email'])
            ->where([
                'role' => 'admin',
                'ici_email_optin' => $ici,
                'cber_email_optin' => $cber,
            ])
            ->all();
    }
}
