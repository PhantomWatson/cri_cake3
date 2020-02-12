<?php
namespace App\Controller;

use App\SurveyMonkey\SurveyMonkey;
use Cake\Event\Event;
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
    /**
     * initialize method
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['cronImport', 'import']);
    }

    /**
     * Method for /surveys/import
     *
     * @param null|int $surveyId Survey ID
     * @return \Cake\Http\Response
     */
    public function import($surveyId = null)
    {
        $this->viewBuilder()->setLayout('blank');
        $importedCount = 0;

        // Disallow import if survey is inactive
        $survey = $this->Surveys->get($surveyId);
        if (! $survey->active) {
            $this->response->withStatus(403);
            $this->set('message', 'This questionnaire is currently inactive. New responses cannot be imported.');

            return $this->render('import');
        }

        // Collect responses
        $responsesTable = TableRegistry::get('Responses');
        $SurveyMonkey = new SurveyMonkey();
        list($success, $responses) = $SurveyMonkey->getNewResponses($surveyId);
        if (! $success) {
            return $this->renderImportError($responses);
        }

        // Loop through each response and add to / update records
        $areasTable = TableRegistry::get('Areas');
        $respondentsTable = TableRegistry::get('Respondents');
        $errorMsgs = [];
        if (is_array($responses)) {
            foreach ($responses as $smRespondentId => $response) {
                $respondent = $SurveyMonkey->extractRespondentInfo($response);
                $name = $respondent['name'] ?: '(no name)';
                $respondentRecord = $respondentsTable->getMatching($surveyId, $respondent, $smRespondentId);
                $serializedResponse = base64_encode(serialize($response));

                // Ignore response if it doesn't include all PWRRR ranks
                $responseRanks = $responsesTable->getResponseRanks($serializedResponse, $survey);
                if (! $responseRanks) {
                    $errorMsgs[] = "Response from $name did not contain all PWR<sup>3</sup> rankings.";
                    continue;
                }

                // Ignore responses if email address is missing or invalid
                if (empty($respondent['email'])) {
                    $errorMsgs[] = "Response from $name is missing an email address.";
                    continue;
                }
                if (! Validation::email($respondent['email'])) {
                    $errorMsgs[] = "Response from $name has an invalid email address: {$respondent['email']}.";
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
                    $errors = $newRespondent->getErrors();
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
                        $errors = $respondentRecord->getErrors();
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
                    'alignment_vs_local' => $alignmentVsLocal,
                    'alignment_vs_parent' => $alignmentVsParent,
                    'response_date' => new Time($response['date_modified']),
                    'aware_of_plan' => $responsesTable->getAwareOfPlan($serializedResponse, $survey)
                ];
                foreach ($responseRanks as $sector => $rank) {
                    $responseFields["{$sector}_rank"] = $rank;
                }
                $newResponse = $responsesTable->newEntity($responseFields);

                $errors = $newResponse->getErrors();
                if (empty($errors)) {
                    $responsesTable->save($newResponse);
                    $importedCount++;
                } else {
                    $errorMsgs[] = "Response from $name is missing required data.";
                    continue;
                }
            }

            /* Set new last_modified_date if there are no errors
             * (if this set of responses contains errors, advancing the last_modified_date
             * would prevent those responses from being imported after those errors are corrected) */
            if (empty($errorMsgs)) {
                $mostRecentDate = $SurveyMonkey->getMostRecentModifiedDate($responses);
                $survey->respondents_last_modified_date = new Time($mostRecentDate);
            }
            $survey->import_errors = $errorMsgs ? serialize($errorMsgs) : null;
            $this->Surveys->save($survey);
        }

        // Finalize
        $this->Surveys->setChecked($surveyId);
        if (empty($errorMsgs)) {
            if ($importedCount) {
                $message = $importedCount . __n(' response', ' responses', $importedCount) . ' imported';
            } else {
                $message = 'No new responses to import';
            }
        } else {
            if ($importedCount) {
                $message = $importedCount . __n(' response', ' responses', $importedCount) . ' imported<br />';
            } else {
                $message = '';
            }
            $message .=
                'Errors prevented the following ' .
                __n('response', 'responses', count($errorMsgs)) .
                ' from being imported:' .
                '<ul>';
            foreach ($errorMsgs as $errorMsg) {
                $message .= '<li>' . $errorMsg . '</li>';
            }
            $message .= '</ul>';
            $this->response->withStatus(500);
        }
        if ($importedCount) {
            $event = new Event('Model.Response.afterImport', $this, ['meta' => [
                'communityId' => $survey->community_id,
                'surveyId' => $survey->id,
                'surveyType' => $survey->type,
                'responseCount' => $importedCount
            ]]);
            $this->getEventManager()->dispatch($event);
        }

        $this->set(compact('message'));
    }

    /**
     * Sets a 500 status code and passes $message to the view
     *
     * @param string $message Error message
     * @return \Cake\Http\Response
     */
    private function renderImportError($message)
    {
        $this->response->withStatus(500);
        $this->set(compact('message'));

        return $this->render('import');
    }

    /**
     * Method for /surveys/get-survey-list
     *
     * @return void
     */
    public function getSurveyList()
    {
        $SurveyMonkey = new SurveyMonkey();
        $result = $SurveyMonkey->getSurveyList($this->request->getQueryParams());
        $this->set([
            'result' => json_encode($result)
        ]);
        $this->viewBuilder()->setLayout('json');
        $this->render('api');
    }

    /**
     * Method for /surveys/get-survey-url
     *
     * @param string|null $smId SurveyMonkey survey ID
     * @return void
     */
    public function getSurveyUrl($smId = null)
    {
        $SurveyMonkey = new SurveyMonkey();
        $this->set([
            'result' => $SurveyMonkey->getSurveyUrl($smId)
        ]);
        $this->viewBuilder()->setLayout('json');
        $this->render('api');
    }

    /**
     * Used by a JS call to find out what community, if any, a survey has already been assigned to
     *
     * @param string|null $smSurveyId SurveyMonkey survey ID
     * @return void
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
        $this->viewBuilder()->setLayout('json');
        $this->set('community', $community);
    }

    /**
     * Method for /surveys/get-qna-ids
     *
     * @param string|null $smId SurveyMonkey survey ID
     * @return void
     */
    public function getQnaIds($smId)
    {
        $SurveyMonkey = new SurveyMonkey();
        $result = $SurveyMonkey->getQuestionAndAnswerIds($smId);
        $this->set('result', json_encode($result));
        $this->viewBuilder()->setLayout('json');
        $this->render('api');
    }

    /**
     * Method for /surveys/cron-import, the target of a cron job
     *
     * @return void
     */
    public function cronImport()
    {
        $surveyId = $this->Surveys->getNextAutoImportCandidate();
        if ($surveyId) {
            $this->import($surveyId);
            $this->Surveys->setChecked($surveyId);
        } else {
            $this->set('message', 'No questionnaires are currently eligible for automatic imports');
            $this->viewBuilder()->setLayout('blank');
        }
        $this->render('import');
    }

    /**
     * Method for clearing this user's save invitation data for the specified survey
     *
     * @param int $surveyId Survey ID
     * @return \Cake\Http\Response
     */
    public function clearSavedInvitationData($surveyId)
    {
        $this->loadComponent('SurveyProcessing');
        $userId = $this->Auth->user('id');
        $success = $this->SurveyProcessing->clearSavedInvitations($surveyId, $userId);
        if (! $success) {
            $this->response->withStatus(500);
        }
        $this->set('result', $success);
        $this->set('_serialize', ['success']);
        $this->viewBuilder()->setLayout('json');

        return $this->render('api');
    }
}
