<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

/**
 * Surveys Controller
 *
 * @property \App\Model\Table\SurveysTable $Surveys
 */
class SurveysController extends AppController
{
    public function import($surveyId = null)
    {
        $this->viewBuilder()->layout('blank');
        $importedCount = 0;

        // Collect respondents
        $respondentsTable = TableRegistry::get('Respondents');
        list($success, $respondents) = $respondentsTable->getNewFromSurveyMonkey($surveyId);
        if (! $success) {
            $this->response->statusCode(500);
            $this->set('message', $respondents);
            return;
        }

        // Convert IDs from integers to strings (the SurveyMonkey API is particular about this)
        $smRespondentIds = array_keys($respondents);
        foreach ($smRespondentIds as &$smRId) {
            $smRId = (string) $smRId;
        }

        // Collect responses
        $responsesTable = TableRegistry::get('Responses');
        list($success, $responses) = $responsesTable->getFromSurveyMonkeyForRespondents($surveyId, $smRespondentIds);
        if (! $success) {
            $this->response->statusCode(500);
            $this->set('message', $responses);
            return;
        }

        // Determine actual ranks (for alignment calculation)
        $area = $this->Surveys->getArea($surveyId);
        $actualRanks = [];
        $sectors = $this->Surveys->getSectors();
        foreach ($sectors as $sector) {
            $actualRanks[$sector] = $area["{$sector}_rank"];
        }

        $survey = $this->Surveys->get($surveyId);
        $usersTable = TableRegistry::get('Users');
        foreach ($responses as $smRespondentId => $response) {
            $respondent = $responsesTable->extractRespondentInfo($response);
            $respondentRecord = $respondentsTable->find('all')
                ->select(['id', 'sm_respondent_id', 'name'])
                ->where([
                    // Same survey and either the same smRespondentId OR (actual) email address
                    'survey_id' => $surveyId,
                    'OR' => [
                        function ($exp, $q) use ($respondent) {
                            // @ and . required, weeds out "email not listed" values
                            return $exp
                                ->like('email', '%@%.%')
                                ->eq('email', $respondent['email']);
                        },
                        ['sm_respondent_id' => $smRespondentId]
                    ]
                ])
                ->first();
            $serializedResponse = base64_encode(serialize($response));

            // Add new respondent
            if (empty($respondentRecord)) {
                $approved = 0;

                // All organization survey responses are auto-approved
                if ($survey->type == 'organization') {
                    $approved = 1;

                // Same with responses from a community's client
                } else {
                    $userId = $usersTable->getIdWithEmail($respondent['email']);
                    if ($userId) {
                        $approved = $usersTable->isCommunityClient($survey->community_id, $userId) ? 1 : 0;
                    }
                }

                $newRespondent = $respondentsTable->newEntity([
                    'email' => $respondent['email'],
                    'name' => $respondent['name'],
                    'survey_id' => $surveyId,
                    'sm_respondent_id' => $smRespondentId,
                    'invited' => false,
                    'approved' => $approved
                ]);
                $errors = $newRespondent->errors();
                if (empty($errors)) {
                    $respondentsTable->save($newRespondent);
                    $respondentId = $newRespondent->id;
                } else {
                    $message = 'Error saving respondent.';
                    $message .= ' Validation errors: '.print_r($errors, true);
                    $this->response->statusCode(500);
                    $this->set(compact('message'));
                    return;
                }

            // Update existing respondent
            } else {
                if (empty($respondentRecord->smRespondentId)) {
                    $respondentRecord = $respondentsTable->patchEntity($respondentRecord, ['sm_respondent_id' => $smRespondentId]);
                }
                if (empty($respondentRecord->name)) {
                    $respondentRecord = $respondentsTable->patchEntity($respondentRecord, ['name' => $respondent['name']]);
                }
                if ($respondentRecord->dirty()) {
                    $respondentsTable->save($respondentRecord);
                }
                $respondentId = $respondentRecord->id;
            }

            // Skip recording response if it's already recorded
            if ($responsesTable->isRecorded($respondentId, $survey, $serializedResponse)) {
                continue;
            }

            // Get individual ranks and alignment
            $responseRanks = $responsesTable->getResponseRanks($serializedResponse, $survey);
            $alignment = $responsesTable->calculateAlignment($actualRanks, $responseRanks);

            // Save response
            $responseFields = [
                'respondent_id' => $respondentId,
                'survey_id' => $surveyId,
                'response' => $serializedResponse,
                'alignment' => $alignment,
                'response_date' => $respondents[$smRespondentId]
            ];
            foreach ($responseRanks as $sector => $rank) {
                $responseFields["{$sector}_rank"] = $rank;
            }
            $newResponse = $responsesTable->newEntity($responseFields);

            $errors = $newResponse->errors();
            if (empty($errors)) {
                $responsesTable->save($newResponse);
                $importedCount++;
            } else {
                $message = 'Error saving response.';
                $message .= ' Validation errors: '.print_r($errors, true);
                $message .= "\n<br />".print_r($newResponse, true);
                $this->response->statusCode(500);
                $this->set(compact('message'));
                return;
            }
        }

        // Finalize
        if ($importedCount) {
            $message = $importedCount.__n(' response', ' responses', $importedCount).' imported';
        } else {
            $message = 'No new responses to import';
        }
        $this->Surveys->setChecked($surveyId);
        $dates = array_values($respondents);
        $survey->respondents_last_modified_date = max($dates);
        $this->Surveys->save($survey);
        $this->set(compact('message'));
    }

    public function getSurveyList()
    {
        $params = $this->request->query;
        $result = $this->Surveys->getSMSurveyList($params);
        $this->set([
            'result' => json_encode($result)
        ]);
        $this->viewBuilder()->layout('json');
        $this->render('api');
    }

    public function getSurveyUrl($smId = null)
    {
        $this->set([
            'result' => $this->Surveys->getSMSurveyUrl($smId)
        ]);
        $this->viewBuilder()->layout('json');
        $this->render('api');
    }

    /**
     * Used by a JS call to find out what community, if any, a survey has already been assigned to
     */
    public function checkSurveyAssignment($smSurveyId = null)
    {
        $survey = $this->Surveys->find('all')
            ->select(['community_id'])
            ->where(['sm_id' => $smSurveyId])
            ->first();
        if (! $survey) {
            $community = null;
        } else {
            $communitiesTable = TableRegistry::get('Communities');
            $community = [
                'id' => $survey->community_id,
                'name' => $communitiesTable->get($survey->community_id)->name
            ];
        }
        $this->viewBuilder()->layout('json');
        $this->set('community', $community);
    }

    public function getQnaIds($smId)
    {
        $result = $this->Surveys->getQuestionAndAnswerIds($smId);
        $this->set('result', json_encode($result));
        $this->viewBuilder()->layout('json');
        $this->render('api');
    }

    public function cronImport()
    {
        $surveyId = $this->Surveys->getNextAutoImportCandidate();
        if ($surveyId) {
            echo 'Importing survey #'.$surveyId.'<br />';
            $this->import($surveyId);
        } else {
            $this->set('message', 'No surveys are currently eligible for automatic imports');
            $this->viewBuilder()->layout('blank');
        }
        $this->render('import');
    }
}
