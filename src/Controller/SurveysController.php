<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validation;

/**
 * Surveys Controller
 *
 * @property \App\Model\Table\SurveysTable $Surveys
 */
class SurveysController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['cronImport', 'import']);
    }

    public function import($surveyId = null)
    {
        $this->viewBuilder()->layout('blank');
        $importedCount = 0;

        // Disallow import if survey is inactive
        $survey = $this->Surveys->get($surveyId);
        if (! $survey->active) {
            $this->response->statusCode(403);
            $this->set('message', 'This questionnaire is currently inactive. New responses cannot be imported.');
            return $this->render('import');
        }

        // Collect respondents
        $respondentsTable = TableRegistry::get('Respondents');
        list($success, $respondents) = $respondentsTable->getNewFromSurveyMonkey($surveyId);
        if (! $success) {
            return $this->renderImportError($respondents);
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
            return $this->renderImportError($responses);
        }

        // Loop through each response and add to / update records
        $areasTable = TableRegistry::get('Areas');
        $usersTable = TableRegistry::get('Users');
        $errorMsgs = [];
        if (is_array($responses)) {
            foreach ($responses as $smRespondentId => $response) {
                $respondent = $responsesTable->extractRespondentInfo($response);
                $name = $respondent['name'] ?: '(no name)';
                $respondentRecord = $respondentsTable->getMatching($surveyId, $respondent, $smRespondentId);
                $serializedResponse = base64_encode(serialize($response));

                // Ignore response if it doesn't include all PWRRR ranks
                $responseRanks = $responsesTable->getResponseRanks($serializedResponse, $survey);
                if (! $responseRanks) {
                    $errorMsgs[] = 'Response from '.$name.' did not contain all PWR<sup>3</sup> rankings.';
                    continue;
                }

                // Ignore responses if email address is missing or invalid
                if (empty($respondent['email'])) {
                    $errorMsgs[] = 'Response from '.$name.' is missing an email address.';
                    continue;
                }
                if (! Validation::email($respondent['email'])) {
                    $errorMsgs[] = 'Response from '.$name.' has an invalid email address: '.$respondent['email'].'.';
                    continue;
                }

                // Add new respondent
                if (empty($respondentRecord)) {
                    $approved = $respondentsTable->isAutoApproved($survey, $respondent['email']);
                    $newRespondent = $respondentsTable->newEntity([
                        'email' => $respondent['email'],
                        'name' => $name,
                        'survey_id' => $surveyId,
                        'sm_respondent_id' => $smRespondentId,
                        'invited' => false,
                        'approved' => $approved ? 1 : 0
                    ]);
                    $errors = $newRespondent->errors();
                    if (empty($errors)) {
                        $respondentsTable->save($newRespondent);
                        $respondentId = $newRespondent->id;
                    } else {
                        /* Don't record anything for this response
                         * (this condition should theoretically never happen,
                         * since any validation errors should have been caught above) */
                        continue;
                    }

                // Update existing respondent
                } else {
                    $newData = [];
                    if (empty($respondentRecord->smRespondentId)) {
                        $newData['sm_respondent_id'] = $smRespondentId;
                    }
                    if (empty($respondentRecord->name)) {
                        $newData['name'] = $respondent['name'];
                    }
                    if (! empty($newData)) {
                        $respondentRecord = $respondentsTable->patchEntity($respondentRecord, $newData);
                        $errors = $respondentRecord->errors();
                        if (empty($errors)) {
                            $respondentsTable->save($respondentRecord);
                        } else {
                            /* Don't record anything for this response
                             * (this condition should theoretically never happen,
                             * since any validation errors should have been caught above) */
                            continue;
                        }
                    }
                    $respondentId = $respondentRecord->id;
                }

                // Skip recording response if it's already recorded
                if ($responsesTable->isRecorded($respondentId, $survey, $serializedResponse)) {
                    continue;
                }

                // Calculate alignment
                $communitiesTable = TableRegistry::get('Communities');
                $community = $communitiesTable->get($survey->community_id);
                $actualRanksLocal = $areasTable->getPwrrrRanks($community->local_area_id);
                $actualRanksParent = $areasTable->getPwrrrRanks($community->parent_area_id);
                $alignmentVsLocal = $responsesTable->calculateAlignment($actualRanksLocal, $responseRanks);
                $alignmentVsParent = $responsesTable->calculateAlignment($actualRanksParent, $responseRanks);

                // Save response
                $responseFields = [
                    'respondent_id' => $respondentId,
                    'survey_id' => $surveyId,
                    'response' => $serializedResponse,
                    'local_area_pwrrr_alignment' => $alignmentVsLocal,
                    'parent_area_pwrrr_alignment' => $alignmentVsParent,
                    'response_date' => new Time($respondents[$smRespondentId])
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
                    $errorMsgs[] = 'Response from '.$name.' is missing required data.';
                    continue;
                }
            }

            /* Set new last_modified_date if there are no errors
             * (if this set of responses contains errors, advancing the last_modified_date
             * would prevent those responses from being imported after those errors are corrected) */
            if (empty($errorMsgs)) {
                $dates = array_values($respondents);
                $survey->respondents_last_modified_date = new Time(max($dates));
            }
            $survey->import_errors = $errorMsgs ? serialize($errorMsgs) : null;
            $this->Surveys->save($survey);
        }

        // Finalize
        $this->Surveys->setChecked($surveyId);
        if (empty($errorMsgs)) {
            $message = $importedCount ? $importedCount.__n(' response', ' responses', $importedCount).' imported' : 'No new responses to import';
        } else {
            $message = $importedCount ? $importedCount.__n(' response', ' responses', $importedCount).' imported<br />' : '';
            $message .= 'Errors prevented the following '.__n('response', 'responses', count($errorMsgs)).' from being imported:';
            $message .= '<ul>';
            foreach ($errorMsgs as $errorMsg) {
                $message .= '<li>'.$errorMsg.'</li>';
            }
            $message .= '</ul>';
            $this->response->statusCode(500);
        }

        $this->set(compact('message'));
    }

    private function renderImportError($message)
    {
        $this->response->statusCode(500);
        $this->set(compact('message'));
        return $this->render('import');
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
            ->select(['community_id', 'type'])
            ->where(['sm_id' => $smSurveyId])
            ->limit(1);
        if ($survey->isEmpty()) {
            $community = null;
        } else {
            $survey = $survey->first();
            $communitiesTable = TableRegistry::get('Communities');
            $community = [
                'id' => $survey->community_id,
                'name' => $communitiesTable->get($survey->community_id)->name,
                'type' => $survey->type
            ];
        }
        $this->viewBuilder()->layout('json');
        $this->set('community', $community);
    }

    public function getQnaIds($smId)
    {
        $result = $this->Surveys->getPwrrrQuestionAndAnswerIds($smId);
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
            $this->Surveys->setChecked($surveyId);
        } else {
            $this->set('message', 'No questionnaires are currently eligible for automatic imports');
            $this->viewBuilder()->layout('blank');
        }
        $this->render('import');
    }
}
