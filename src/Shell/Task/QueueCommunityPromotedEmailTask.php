<?php
declare(strict_types=1);

namespace App\Shell\Task;

use Cake\Mailer\MailerAwareTrait;
use Queue\Shell\Task\QueueTask;

class QueueCommunityPromotedEmailTask extends QueueTask
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
        try {
            $this->getMailer('Community')->send('communityPromoted', [
                $data['user'],
                $data['community'],
                $data['toStep'],
            ]);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
