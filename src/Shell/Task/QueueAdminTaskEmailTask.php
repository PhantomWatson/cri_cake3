<?php
namespace App\Shell\Task;

use Cake\Mailer\MailerAwareTrait;
use Cake\Network\Exception\InternalErrorException;
use Queue\Shell\Task\QueueTask;

class QueueAdminTaskEmailTask extends QueueTask
{
    use MailerAwareTrait;

    /**
     * Outputs a message explaining that this task cannot be added via CLI
     *
     * @return void
     */
    public function add()
    {
        $this->err('Task cannot be added via console');
    }

    /**
     * Run function.
     * This function is executed, when a worker is executing a task.
     * The return parameter will determine, if the task will be marked completed, or be requeued.
     *
     * @param array $data The array passed to QueuedTask->createJob()
     * @param int $id The id of the QueuedTask
     * @return bool Success
     */
    public function run(array $data, $id)
    {
        $mailerMethodName = $this->getMailerMethodName($data['eventName']);
        try {
            $this->getMailer('AdminTask')->send($mailerMethodName, [$data]);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns the name of the email template corresponding to the specified event name
     *
     * @param string $eventName Event name
     * @return string
     * @throws InternalErrorException
     */
    private function getMailerMethodName($eventName)
    {
        $mailTemplates = [
            'Model.Survey.afterDeactivate' => 'deliverPresentation',
            'Model.Product.afterPurchase' => 'deliverOptionalPresentation',
            'Model.Purchase.afterAdminAdd' => 'deliverOptionalPresentation'
        ];

        if (array_key_exists($eventName, $mailTemplates)) {
            return $mailTemplates[$eventName];
        }

        throw new InternalErrorException('Unrecognized event name: ' . $eventName);
    }
}
