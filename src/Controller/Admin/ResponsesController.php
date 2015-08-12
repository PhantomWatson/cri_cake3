<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class ResponsesController extends AppController
{
    private function adminViewPagination($surveyId)
    {
        $this->paginate['Response'] = [
            'conditions' => ['survey_id' => $surveyId],
            'contain' => [
                'Respondent' => [
                    'fields' => ['id', 'email', 'name', 'approved']
                ]
            ],
            'order' => ['response_date' => 'DESC']
        ];
        $count = $this->Responses->find('all')
            ->where(['survey_id' => $surveyId])
            ->count();
        if ($count) {
            $this->paginate['Response']['limit'] = $count;
        }
        $this->cookieSort('AdminResponsesView');
    }
}
