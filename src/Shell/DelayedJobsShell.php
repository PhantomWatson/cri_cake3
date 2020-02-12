<?php
declare(strict_types=1);

namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class DelayedJobsShell extends Shell
{
    private $threshold = '15 minutes ago';

    /**
     * Display help for this console.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addSubcommand('alert', [
            'help' => 'Checks and sends alerts for delayed jobs',
        ]);
        $parser->addSubcommand('check', [
            'help' => 'Checks for delayed jobs',
        ]);

        return $parser;
    }

    /**
     * Sends alerts to Slack when jobs are delayed more than $this->threshold
     *
     * @return void
     */
    public function alert()
    {
        $results = $this->getDelayedJobs();

        if ($results->isEmpty()) {
            $this->out('No alerts sent (no jobs created more than ' . $this->threshold . ')');

            return;
        }

        $count = $results->count();
        $msg = $count . __n(' job has', ' jobs have', $count) .
            ' been created more than ' . $this->threshold . ' and haven\'t been processed yet.' . "\n" .
            'Visit https://cri.cberdata.org/admin/queue for details.';
        $data = 'payload=' . json_encode([
            'channel' => '#cri',
            'text' => $msg,
            'icon_emoji' => ':robot_face:',
            'username' => 'CBER Web Server',
        ]);

        // You can get your webhook endpoint from your Slack settings
        $url = Configure::read('slack_webhook_url');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);

        $msg = 'Alert sent (' . $count .
            __n(' job', ' jobs', $count) . ' created more than ' . $this->threshold . ')';
        $this->out($msg);
    }

    /**
     * Displays all of the jobs currently delayed more than $this->threshold
     *
     * @return void
     */
    public function check()
    {
        $results = $this->getDelayedJobs();
        $delayedJobs = [['Type', 'Reference', 'Created']];
        foreach ($results as $result) {
            $waiting = $result->created->timeAgoInWords();
            $delayedJobs[] = [
                'type' => $result['job_type'],
                'reference' => $result['reference'],
                'created' => $waiting,
            ];
        }

        if (! $results->isEmpty()) {
            $this->out('Jobs created more than ' . $this->threshold . ':');
            $this->helper('Table')->output($delayedJobs);
        } else {
            $this->out('No jobs created more than ' . $this->threshold . '.');
        }
    }

    /**
     * Returns any uncomplted jobs created before $this->>threshold
     *
     * @return \Cake\Datasource\ResultSetInterface
     */
    private function getDelayedJobs()
    {
        /** @var \Queue\Model\Table\QueuedJobsTable $queuedJobsTable */
        $queuedJobsTable = TableRegistry::getTableLocator()->get('QueuedJobs');
        $createdEarlierThan = date('Y-m-d H:i:a', strtotime($this->threshold));

        return $queuedJobsTable->find()
            ->where(function ($exp) use ($createdEarlierThan) {
                /** @var \Cake\Database\Expression\QueryExpression $exp */

                return $exp
                    ->lt('created', $createdEarlierThan)
                    ->isNull('completed');
            })
            ->orderAsc('created')
            ->all();
    }
}
