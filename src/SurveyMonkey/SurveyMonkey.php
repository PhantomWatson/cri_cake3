<?php
declare(strict_types=1);

namespace App\SurveyMonkey;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\NotFoundException;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validation;

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
        $path = ROOT . DS . 'vendor' . DS . 'liogi' . DS . 'surveymonkey-api-v3' . DS . 'lib';
        require_once $path . DS . 'SurveyMonkey.php';
        $apiKey = Configure::read('survey_monkey_api_key');
        $accessToken = Configure::read('survey_monkey_api_access_token');

        $this->api = new \SurveyMonkey($apiKey, $accessToken);
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
     * Returns responses for the specified survey
     *
     * @param string $smSurveyId SurveyMonkey (not CRI) survey ID
     * @param array $params Query parameters
     * @return mixed
     */
    public function getResponses($smSurveyId, $params = [])
    {
        return $this->api->getResponses($smSurveyId, $params);
    }

    /**
     * Returns a list of collectors for the specified survey
     *
     * @param string $smSurveyId SurveyMonkey (not CRI) survey ID
     * @param array $params Query parameters
     * @return mixed
     */
    public function getCollectorList($smSurveyId, $params = [])
    {
        return $this->api->getCollectorList($smSurveyId, $params);
    }

    /**
     * Returns details about the specified survey
     *
     * @param string $smSurveyId SurveyMonkey (not CRI) survey ID
     * @return mixed
     */
    public function getSurveyDetails($smSurveyId)
    {
        return $this->api->getSurveyDetails($smSurveyId);
    }

    /**
     * Returns a list of surveys
     *
     * @param array $params Query parameters
     * @return mixed
     */
    public function getSurveyList($params)
    {
        $pageSize = 1000;
        $page = 1;
        $retval = [];
        while (true) {
            $defaultParams = [
                'page' => $page,
                'per_page' => $pageSize,
            ];
            $params = array_merge($defaultParams, $params);
            $result = $this->api->getSurveyList($params);
            if (isset($result['data']['data']) && ! empty($result['data']['data'])) {
                foreach ($result['data']['data'] as $survey) {
                    $retval[] = [
                        'sm_id' => $survey['id'],
                        'title' => $survey['title'],
                        'url' => $this->getCachedSMSurveyUrl($survey['id']),
                    ];
                }
                if (count($result['data']['data']) == $pageSize) {
                    $page++;
                } else {
                    break;
                }
            } else {
                break;
            }
        }

        return $retval;
    }

    /**
     * Uses the SurveyMonkey API to determine the SurveyMonkey respondent
     * id (aka response ID) corresponding to a CRI respondent ID
     *
     * @param int $respondentId Respondent ID
     * @return string
     * @throws \Cake\Network\Exception\InternalErrorException
     */
    public function getSmRespondentId($respondentId)
    {
        $respondentsTable = TableRegistry::getTableLocator()->get('Respondents');
        /** @var \App\Model\Entity\Respondent $respondent */
        $respondent = $respondentsTable->find('all')
            ->select(['email', 'survey_id'])
            ->where(['id' => $respondentId])
            ->first();
        $email = $respondent->email;
        $surveyId = $respondent->survey_id;

        $responsesTable = TableRegistry::getTableLocator()->get('Responses');
        /** @var \App\Model\Entity\Response $response */
        $response = $responsesTable->find('all')
            ->select(['response_date'])
            ->where(['respondent_id' => $respondentId])
            ->order(['response_date' => 'DESC'])
            ->first();
        $responseDate = $response->response_date->i18nFormat('yyyy-MM-ddTHH:mm:ss');

        $surveysTable = TableRegistry::getTableLocator()->get('Surveys');
        /** @var \App\Model\Entity\Survey $survey */
        $survey = $surveysTable->find('all')
            ->select(['sm_id'])
            ->where(['id' => $surveyId])
            ->first();
        $smSurveyId = $survey->sm_id;

        $result = $this->getRespondentList((string)$smSurveyId, [
            'start_modified_at' => $responseDate,
        ]);
        if (! $result['success']) {
            $msg = 'Error retrieving response data from SurveyMonkey.';
            $msg .= ' Details: ' . print_r($result['message'], true);
            throw new InternalErrorException($msg);
        }

        foreach ($result['data']['data'] as $returnedRespondent) {
            $respondent = $this->extractRespondentInfo($returnedRespondent);
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
     * @throws \Cake\Http\Exception\NotFoundException
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

    /**
     * Returns a SurveyMonkey survey URL from the cache
     *
     * @param string $smId SurveyMonkey survey ID
     * @return string|null
     */
    public function getCachedSMSurveyUrl($smId)
    {
        return Cache::read($smId, 'survey_urls');
    }

    /**
     * Returns the URL for a SurveyMonkey survey
     *
     * @param string $smSurveyId SurveyMonkey-defined survey ID
     * @return string
     * @throws \Cake\Http\Exception\NotFoundException
     */
    public function getSurveyUrl($smSurveyId = null)
    {
        // Validate ID
        if (! $smSurveyId) {
            throw new NotFoundException('SurveyMonkey ID not specified');
        } elseif (! is_numeric($smSurveyId)) {
            throw new NotFoundException("SurveyMonkey ID '$smSurveyId' is not numeric");
        }

        // Pull from cache if possible
        $cached = $this->getCachedSMSurveyUrl($smSurveyId);
        if ($cached) {
            return $cached;
        }

        // Nab URL from SurveyMonkey
        $params = ['include' => 'type,url'];
        $collectors = $this->getCollectorList((string)$smSurveyId, $params);
        $retval = false;
        if (isset($collectors['data']['data']) && ! empty($collectors['data']['data'])) {
            foreach ($collectors['data']['data'] as $collector) {
                if ($collector['type'] == 'weblink') {
                    $retval = $collector['url'];
                    break;
                }
            }
        }

        if (empty($retval)) {
            throw new NotFoundException("SurveyMonkey survey #$smSurveyId URL not found");
        } else {
            Cache::write($smSurveyId, $retval, 'survey_urls');

            return $retval;
        }
    }

    /**
     * Returns an array with values for 'name' and 'email'
     *
     * @param array $response Response array
     * @return array
     */
    public function extractRespondentInfo($response)
    {
        $retval = [
            'name' => '',
            'email' => '',
        ];

        // Assume the first field contains the respondent's name
        if (isset($response['pages'][0]['questions'][0]['answers'][0]['text'])) {
            $retval['name'] = $response['pages'][0]['questions'][0]['answers'][0]['text'];
        }

        // Search for the first response that's a valid email address
        foreach ($response['pages'][0]['questions'] as $section) {
            foreach ($section['answers'] as $answer) {
                if (! isset($answer['text'])) {
                    continue;
                }
                $answerText = trim($answer['text']);
                if (Validation::email($answerText)) {
                    $retval['email'] = strtolower($answerText);

                    return $retval;
                }
            }
        }

        return $retval;
    }

    /**
     * Retrieves new SurveyMonkey responses
     *
     * @param int $surveyId Survey ID
     * @return array [success / fail, responses / error message]
     */
    public function getNewResponses($surveyId)
    {
        $surveysTable = TableRegistry::getTableLocator()->get('Surveys');
        try {
            $survey = $surveysTable->get($surveyId);
        } catch (RecordNotFoundException $e) {
            return [false, "Questionnaire #$surveyId not found"];
        }

        if (!$survey->sm_id) {
            return [false, "Questionnaire #$surveyId has not yet been linked to SurveyMonkey"];
        }

        $params = ['status' => 'completed'];
        if ($survey->respondents_last_modified_date) {
            $lastResponseDate = $survey->respondents_last_modified_date->format('Y-m-d H:i:s');
            $timestamp = strtotime($lastResponseDate);
            $params['start_modified_at'] = date('Y-m-d\TH:i:s', $timestamp + 1);
        }

        $result = $this->getResponses((string)$survey->sm_id, $params);

        if (!$result) {
            return [true, 'No new respondents'];
        }

        $retval = [];
        foreach ($result as $response) {
            $smRespondentId = $response['id'];
            $retval[$smRespondentId] = $response;
        }

        return [true, $retval];
    }

    /**
     * Queries SurveyMonkey to determine the IDs associated with relevant questions and answers
     *
     * @param string $smId SurveyMonkey survey ID
     * @return array First value is true/false for success/failure, second value is status message, third is data array for saving Survey with
     */
    public function getQuestionAndAnswerIds($smId)
    {
        $result = $this->getSurveyDetails((string)$smId);
        if (! isset($result['data'])) {
            return [
                false,
                'Could not get questionnaire details from SurveyMonkey. This might be a temporary network error.'
            ];
        }

        // Create an array to save this data with
        /** @var \App\Model\Table\SurveysTable $surveysTable */
        $surveysTable = TableRegistry::getTableLocator()->get('Surveys');
        $sectors = $surveysTable->getSectors();
        $qnaIdFields = $surveysTable->getQnaIdFieldNames();
        $nulls = array_fill(0, count($qnaIdFields), null);
        $data = array_combine($qnaIdFields, $nulls);

        // Find the PWRRR-ranking question and store its corresponding Q&A IDs in $data
        $keyPhrases = [
            'PWR3 is a tool for thinking about the economic future of your community.',
            'Each Indiana community uses a combination of 5 activities',
        ];
        $pwrrrQuestion = $this->findQuestion($result, $keyPhrases);
        if (! $pwrrrQuestion) {
            return [
                false,
                'Error: This questionnaire does not contain a PWR<sup>3</sup> ranking question.',
            ];
        }
        $data['pwrrr_qid'] = $pwrrrQuestion['id'];
        foreach ($pwrrrQuestion['answers'] as $choices) {
            foreach ($choices as $choice) {
                // For some reason, in_array($answer['text'], range('1', '5')) doesn't work
                switch ($choice['text']) {
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                        $field = $choice['text'] . '_aid';
                        $data[$field] = $choice['id'];
                        break;
                }
                foreach ($sectors as $sector) {
                    if (stripos($choice['text'], $sector) !== false) {
                        $field = $sector . '_aid';
                        $data[$field] = $choice['id'];
                        break;
                    }
                }
            }
        }

        /* Find the "aware of comprehensive community plan" question (for officials surveys)
         * and store its corresponding Q&A IDs in $data */
        $keyPhrases = ['Please check the box for each comprehensive community plan'];
        $awareOfPlanQuestion = $this->findQuestion($result, $keyPhrases);
        if ($awareOfPlanQuestion) {
            $data['aware_of_plan_qid'] = $awareOfPlanQuestion['id'];
            $keyPhrases = [
                'City' => 'aware_of_city_plan_aid',
                'County' => 'aware_of_county_plan_aid',
                'Regional' => 'aware_of_regional_plan_aid',
                'I do not know' => 'unaware_of_plan_aid',
            ];
            foreach ($awareOfPlanQuestion['answers']['choices'] as $choice) {
                foreach ($keyPhrases as $phrase => $field) {
                    if (strpos($choice['text'], $phrase) !== false) {
                        $data[$field] = $choice['id'];
                        break;
                    }
                }
            }
        }

        return [true, '', $data];
    }

    /**
     * Returns a full question array with matching heading text
     *
     * @param array $surveyDetails Result of survey details API call
     * @param string[] $keyPhrases Array of phrases to search for
     * @return null|array
     */
    public function findQuestion($surveyDetails, array $keyPhrases)
    {
        foreach ($surveyDetails['data']['pages'] as $page) {
            foreach ($page['questions'] as $question) {
                foreach ($keyPhrases as $keyPhrase) {
                    $heading = $question['headings'][0]['heading'];
                    if (strpos($heading, $keyPhrase) !== false) {
                        return $question;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Takes the results of a SurveyMonkey API call and returns
     * the most recent modified date in 'Y-m-d H:i:s' format
     *
     * @param array $smResponses Result of call to /surveys/{id}/responses/bulk
     * @return false|string
     */
    public function getMostRecentModifiedDate($smResponses)
    {
        $dates = [];
        foreach ($smResponses as $smResponseId => $response) {
            $dates[] = $response['date_modified'];
        }
        $mostRecentDate = max($dates);
        $timestamp = strtotime($mostRecentDate);

        return date('Y-m-d H:i:s', $timestamp);
    }
}
