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
     * @param array|null $data Metadata to include in queued job in addition to default data
     * @throws \Exception
     * @return \Cake\ORM\Entity Saved job entity
     */
    public function enqueueEmail($recipient, $data = null)
    {
        $data['user']['email'] = $recipient->email;
        $data['user']['name'] = $recipient->name;
        $data['community']['id'] = $this->community->id;
        $data['community']['name'] = $this->community->name;

        return $this->queuedJobs->createJob(
            'AdminTaskEmail',
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
    public function sendToGroup($alertName, $data)
    {
        $data['alert'] = $alertName;
        $adminGroupName = $this->alertRecipients->getUserGroup($alertName);
        $recipients = $this->alertRecipients->getRecipients($adminGroupName);
        foreach ($recipients as $recipient) {
            $this->enqueueEmail($recipient, $data);
        }
    }

    /**
     * Sends the specified alert only if it's valid for the current community
     *
     * @param string $alertName Such as createClients or deliverPolicyDev
     * @param array $data Queued job metadata
     * @throws \Exception
     * @return void
     */
    public function sendIfValid($alertName, $data = [])
    {
        $alertable = new Alertable($this->community->id);
        if ($alertable->{$alertName}()) {
            $this->sendToGroup($alertName, $data);
        }
    }
}
