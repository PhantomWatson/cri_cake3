<?php
namespace App\Controller\Client;

use App\Controller\AppController;
use Cake\Network\Exception\BadRequestException;

class RespondentsController extends AppController
{
    public function index($surveyType = null)
    {
        if ($surveyType != 'official' && $surveyType != 'organization') {
            throw new BadRequestException('Survey type not specified');
        }

        $clientId = $this->getClientId();
        $communitiesTable = TableRegistry::get('Communities');
        $communityId = $communitiesTable->getClientCommunityId($clientId);
        if ($communityId) {
            $community = $communitiesTable->get($communityId);
            $titleForLayout = $community->name.' '.ucwords($surveyType).' Survey Respondents';
            $this->setupPagination($community->id, $surveyType);
            $respondents = $this->paginate();
        } else {
            $titleForLayout = 'Survey Respondents';
            $respondents = [];
        }
        $this->set(compact(
            'titleForLayout',
            'respondents',
            'surveyType'
        ));
    }
}
