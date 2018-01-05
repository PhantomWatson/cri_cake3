<?php
namespace App\Mailer\Preview;

use App\Mailer\AdminTaskMailer;
use App\Model\Table\ProductsTable;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use DebugKit\Mailer\MailPreview;

class AdminTaskEmailPreview extends MailPreview
{
    /**
     * Preview method for CommunityMailer::deliverMandatoryPresentation()
     *
     * @return Email
     */
    public function deliverMandatoryPresentation()
    {
        $data = [
            'user' => [
                'name' => 'Recipient Name',
                'email' => 'recipient@example.com'
            ],
            'community' => ['name' => 'Community Name'],
            'surveyType' => 'official'
        ];

        /** @var AdminTaskMailer $mailer */
        $mailer = $this->getMailer('AdminTask');

        return $mailer->deliverMandatoryPresentation($data);
    }

    /**
     * Preview method for CommunityMailer::deliverOptionalPresentation()
     *
     * @return Email
     */
    public function deliverOptionalPresentation()
    {
        $data = [
            'user' => [
                'name' => 'Recipient Name',
                'email' => 'recipient@example.com'
            ],
            'community' => ['name' => 'Community Name'],
            'productId' => ProductsTable::OFFICIALS_SUMMIT
        ];

        /** @var AdminTaskMailer $mailer */
        $mailer = $this->getMailer('AdminTask');

        return $mailer->deliverOptionalPresentation($data);
    }

    /**
     * Preview method for CommunityMailer::createSurvey()
     *
     * @return Email
     */
    public function createSurvey()
    {
        $data = [
            'user' => [
                'name' => 'Recipient Name',
                'email' => 'recipient@example.com'
            ],
            'community' => [
                'name' => 'Community Name',
                'slug' => 'community-name'
            ],
            'newSurveyType' => 'official',
            'toStep' => 2
        ];

        /** @var AdminTaskMailer $mailer */
        $mailer = $this->getMailer('AdminTask');

        return $mailer->createSurvey($data);
    }

    /**
     * Preview method for CommunityMailer::createSurveyNewCommunity()
     *
     * @return Email
     */
    public function createSurveyNewCommunity()
    {
        $data = [
            'user' => [
                'name' => 'Recipient Name',
                'email' => 'recipient@example.com'
            ],
            'community' => [
                'name' => 'Community Name',
                'slug' => 'community-name'
            ]
        ];

        /** @var AdminTaskMailer $mailer */
        $mailer = $this->getMailer('AdminTask');

        return $mailer->createSurveyNewCommunity($data);
    }

    /**
     * Preview method for CommunityMailer::schedulePresentation()
     *
     * @return Email
     */
    public function schedulePresentation()
    {
        $communitiesTable = TableRegistry::get('Communities');
        $data = [
            'user' => [
                'name' => 'Recipient Name',
                'email' => 'recipient@example.com'
            ],
            'community' => [
                'id' => $communitiesTable->find()->first()->id,
                'name' => 'Community Name'
            ],
            'deliverableId' => ProductsTable::OFFICIALS_SUMMIT
        ];

        /** @var AdminTaskMailer $mailer */
        $mailer = $this->getMailer('AdminTask');

        return $mailer->schedulePresentation($data);
    }

    /**
     * Preview method for CommunityMailer::deliverPolicyDev()
     *
     * @return Email
     */
    public function deliverPolicyDev()
    {
        $communitiesTable = TableRegistry::get('Communities');
        $data = [
            'user' => [
                'name' => 'Recipient Name',
                'email' => 'recipient@example.com'
            ],
            'community' => [
                'id' => $communitiesTable->find()->matching('Clients')->first()->id,
                'name' => 'Community Name'
            ]
        ];

        /** @var AdminTaskMailer $mailer */
        $mailer = $this->getMailer('AdminTask');

        return $mailer->deliverPolicyDev($data);
    }

    /**
     * Preview method for CommunityMailer::assignClient()
     *
     * @return Email
     */
    public function assignClient()
    {
        $communitiesTable = TableRegistry::get('Communities');
        $data = [
            'user' => [
                'name' => 'Recipient Name',
                'email' => 'recipient@example.com'
            ],
            'community' => [
                'id' => $communitiesTable->find()->matching('Clients')->first()->id,
                'name' => 'Community Name'
            ]
        ];

        /** @var AdminTaskMailer $mailer */
        $mailer = $this->getMailer('AdminTask');

        return $mailer->assignClient($data);
    }

    /**
     * Preview method for CommunityMailer::activateSurvey()
     *
     * @return Email
     */
    public function activateSurvey()
    {
        $data = [
            'user' => [
                'name' => 'Recipient Name',
                'email' => 'recipient@example.com'
            ],
            'community' => [
                'name' => 'Community Name'
            ],
            'surveyType' => 'organization'
        ];

        /** @var AdminTaskMailer $mailer */
        $mailer = $this->getMailer('AdminTask');

        return $mailer->activateSurvey($data);
    }
}
