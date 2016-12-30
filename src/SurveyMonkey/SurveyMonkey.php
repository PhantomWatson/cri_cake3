<?php
namespace App\SurveyMonkey;

use Cake\Core\Configure;
use Cake\Network\Exception\InternalErrorException;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

/**
 * Utility class for using the SurveyMonkey API
 * and processing the results of API calls
 *
 * Class SurveyMonkey
 * @package App\SurveyMonkey
 */
class SurveyMonkey
{
    public $api;

    /**
     * SurveyMonkey constructor.
     */
    public function __construct()
    {
        $this->api = $this->getSurveyMonkeyObject();
    }

    /**
     * Returns a SurveyMonkey object
     *
     * Uses liogi/surveymonkey-api-v3
     *
     * @return SurveyMonkey
     */
    public function getSurveyMonkeyObject()
    {
        $path = ROOT . DS . 'vendor' . DS . 'liogi' . DS . 'surveymonkey-api-v3' . DS . 'lib';
        require_once $path . DS . 'SurveyMonkey.php';
        $apiKey = Configure::read('survey_monkey_api_key');
        $accessToken = Configure::read('survey_monkey_api_access_token');

        return new \SurveyMonkey($apiKey, $accessToken);
    }

    /**
     * Returns a list of respondents for the specified survey
     *
     * @param string $smSurveyId SurveyMonkey (not CRI) survey ID
     * @param array $params Query parameters
     * @return mixed
     */
    public function getRespondentList($smSurveyId, $params = [])
    {
        return $this->api->getRespondentList($smSurveyId, $params);
    }

    /**
     * Uses the SurveyMonkey API to determine the SurveyMonkey respondent
     * id (aka response ID) corresponding to a CRI respondent ID
     *
     * @param int $respondentId Respondent ID
     * @return string
     * @throws InternalErrorException
     */
    public function getSmRespondentId($respondentId)
    {
        $respondent = $this->find('all')
            ->select(['email', 'survey_id'])
            ->where(['id' => $respondentId])
            ->first();
        $email = $respondent->email;
        $surveyId = $respondent->survey_id;

        $responsesTable = TableRegistry::get('Responses');
        $response = $responsesTable->find('all')
            ->select(['response_date'])
            ->where(['respondent_id' => $respondentId])
            ->order(['response_date' => 'DESC'])
            ->first();
        $responseDate = $response->response_date->i18nFormat('yyyy-MM-dd HH:mm:ss');

        $surveysTable = TableRegistry::get('Surveys');
        $survey = $surveysTable->find('all')
            ->select(['sm_id'])
            ->where(['id' => $surveyId])
            ->first();
        $smSurveyId = $survey->sm_id;

        $SurveyMonkey = new SurveyMonkey();
        $result = $SurveyMonkey->getRespondentList((string)$smSurveyId, [
            'start_modified_at' => $responseDate
        ]);
        if (! $result['success']) {
            $msg = 'Error retrieving response data from SurveyMonkey.';
            $msg .= ' Details: ' . print_r($result['message'], true);
            throw new InternalErrorException($msg);
        }

        foreach ($result['data']['data'] as $returnedRespondent) {
            $respondent = $responsesTable->extractRespondentInfo($returnedRespondent);
            if ($respondent['email'] == $email) {
                return $returnedRespondent['id'];
            }
        }
        throw new NotFoundException('SurveyMonkey didn\'t return any data about this respondent');
    }

    /**
     * Returns an array containing question => answer pairs for the
     * most recent response for the specified respondent
     *
     * @param string $smSurveyId SurveyMonkey survey ID
     * @param string $smRespondentId SurveyMonkey respondent ID
     * @return array
     * @throws NotFoundException
     */
    public function getFullResponse($smSurveyId, $smRespondentId)
    {
        $result = $this->api->getResponse((string)$smSurveyId, (string)$smRespondentId);
        if (! $result['success'] || empty($result['data'])) {
            throw new NotFoundException("Could not find a response for respondent #$smRespondentId");
        }
        $response = $result['data'];

        $result = $this->api->getSurveyDetails((string)$smSurveyId);
        if (! $result['success'] || empty($result['data'])) {
            throw new NotFoundException("Could not find questionnaire data for questionnaire #$smSurveyId");
        }
        $survey = $result['data'];

        // Get question IDs
        $questions = [];
        foreach ($survey['pages'] as $page) {
            foreach ($page['questions'] as $q) {
                $questions[$q['id']] = $q['headings'][0]['heading'];
            }
        }

        // Get multiple-choice answer IDs
        $choices = [];
        foreach ($survey['pages'] as $page) {
            foreach ($page['questions'] as $q) {
                if (! isset($q['answers'])) {
                    continue;
                }

                $answers = $q['answers'];
                if (isset($answers['rows'])) {
                    foreach ($answers['rows'] as $a) {
                        $choices[$a['id']] = $a['text'];
                    }
                }
                if (isset($answers['choices'])) {
                    foreach ($answers['choices'] as $a) {
                        $choices[$a['id']] = $a['text'];
                    }
                }
                if (isset($answers['other'])) {
                    $choices[$answers['other']['id']] = $answers['other']['text'];
                }
            }
        }

        $retval = [];
        foreach ($response['pages'] as $page) {
            foreach ($page['questions'] as $q) {
                $questionLabel = $questions[$q['id']];
                $answers = [];
                foreach ($q['answers'] as $a) {
                    $answer = '';

                    // For multiple-choice answers, display "Row text: Choice text"
                    if (isset($a['row_id']) && $a['row_id']) {
                        $answer .= $choices[$a['row_id']] . ': ';
                    }
                    if (isset($a['choice_id']) && $a['choice_id']) {
                        $answer .= $choices[$a['choice_id']];
                    }

                    // Otherwise, just display typed-in answer text
                    if (isset($a['text']) && $a['text']) {
                        $answer .= $a['text'];
                    }
                    $answers[] = $answer;
                }
                $retval[$questionLabel] = $answers;
            }
        }

        return $retval;
    }
}
