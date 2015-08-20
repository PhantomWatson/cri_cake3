<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class SurveysController extends AppController
{
    public function index()
    {
        $communitiesTable = TableRegistry::get('Communities');
        $clientCommunities = $this->Community->getClientCommunityList();
        $clientCommunityIds = array_keys($clientCommunities);
        $this->paginate['Community'] = [
            'conditions' => ['id' => $clientCommunityIds],
            'contain' => [
                'OfficialSurvey' => [
                    'fields' => ['id']
                ],
                'OrganizationSurvey' => [
                    'fields' => ['id']
                ]
            ],
            'fields' => ['id', 'name', 'score']
        ];
        $this->set([
            'titleForLayout' => 'Surveys',
            'communities' => $this->paginate('Community')
        ]);
    }
}
