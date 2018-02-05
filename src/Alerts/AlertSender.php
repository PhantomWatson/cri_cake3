<?php
namespace App\Alerts;

use App\Model\Entity\Community;
use App\Model\Entity\User;
use App\Model\Table\CommunitiesTable;
use App\Model\Table\DeliveriesTable;
use App\Model\Table\ProductsTable;
use App\Model\Table\SurveysTable;
use App\Model\Table\UsersTable;
use Cake\ORM\TableRegistry;
use DateTime;
use Queue\Model\Table\QueuedJobsTable;

/**
 * Class AlertSender
 * @package App\Alerts
 * @property CommunitiesTable $communities
 * @property Community $community
 * @property DeliveriesTable $deliveries
 * @property ProductsTable $products
 * @property QueuedJobsTable $queuedJobs
 * @property SurveysTable $surveys
 * @property UsersTable $users
 */
class AlertSender
{
    private $communities;
    private $community;
    private $queuedJobs;
    private $users;
    private $alertRecipients;

    /**
     * Alertable constructor.
     *
     * @param int $communityId Community ID
     */
    public function __construct($communityId)
    {
        $this->communities = TableRegistry::get('Communities');
        $this->queuedJobs = TableRegistry::get('Queue.QueuedJobs');
        $this->users = TableRegistry::get('Users');
        $this->alertRecipients = new AlertRecipients();

        $this->community = $this->communities->get($communityId);
    }

    /**
     * Enqueues an alert email
     *
     * @param User $recipient Alert recipients
     * @param string $alertName Such as createClients or deliverPolicyDev
     * @param array|null $data Metadata to include in queued job in addition to default data
     * @throws \Exception
     * @return bool
     */
    public function enqueueEmail($recipient, $alertName, $data = null)
    {
        $data['user']['email'] = $recipient->email;
        $data['user']['name'] = $recipient->name;
        $data['community']['id'] = $this->community->id;
        $data['community']['name'] = $this->community->name;
        $data['alert'] = $alertName;

        return (bool)$this->queuedJobs->createJob(
            'AdminAlertEmail',
            $data,
            ['reference' => $recipient->email]
        );
    }

    /**
     * Sends alerts to all members of the CBER group, ICI group, or both
     *
     * @param string $alertName Such as createClients or deliverPolicyDev
     * @param array $data Queued job metadata
     * @return void
     * @throws \Exception
     */
    public function sendToGroup($alertName, $data = [])
    {
        $recipients = $this->alertRecipients->getRecipients($alertName);
        foreach ($recipients as $recipient) {
            $this->enqueueEmail($recipient, $alertName, $data);
        }
    }

    /**
     * Sends the specified alert only if it's valid for the current community and if each recipient has not received
     * the alert too recently.
     *
     * @param string $alertName Such as createClients or deliverPolicyDev
     * @param array $data Queued job metadata
     * @throws \Exception
     * @return void
     */
    public function sendIfValid($alertName, $data = [])
    {
        $alertable = new Alertable($this->community->id);
        if (!$alertable->{$alertName}()) {
            return;
        }

        $recipients = $this->alertRecipients->getRecipients($alertName);
        foreach ($recipients as $recipient) {
            if ($this->isRecentlySent($recipient->email, $alertName)) {
                continue;
            }
            $this->enqueueEmail($recipient, $alertName, $data);
        }
    }

    /**
     * Returns the DateTime of the most recent time the specified alert was sent within a given threshold, or FALSE
     * if no such alert was sent within that threshold
     *
     * @param string $email Recipient email address
     * @param string $alertName Name of mailer method for alert
     * @return DateTime|bool
     */
    public function isRecentlySent($email, $alertName)
    {
        $threshold = '-7 days';
        $queuedJobsTable = TableRegistry::get('Queue.QueuedJobs');
        $recentEmails = $queuedJobsTable->find()
            ->select(['created', 'data'])
            ->where([
                'job_type' => 'AdminAlertEmail',
                'reference' => $email,
                'created >=' => new DateTime($threshold)
            ])
            ->orderDesc('created')
            ->all();

        if ($recentEmails->isEmpty()) {
            return false;
        }

        foreach ($recentEmails as $recentEmail) {
            $data = unserialize($recentEmail['data']);
            $isMatch = isset($data['community']['id']) &&
                $data['community']['id'] == $this->community->id &&
                isset($data['alert']) &&
                $data['alert'] == $alertName;
            if ($isMatch) {
                return $recentEmail['created'];
            }
        }

        return false;
    }
}
