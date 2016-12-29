<?php
namespace App\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Behavior;

class SurveyMonkeyBehavior extends Behavior
{
    /**
     * Returns a SurveyMonkey object
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
     * Returns the (trimmed) email address of the respondent who
     * filled out this response returned by the SurveyMonkey API,
     * or FALSE if not found
     *
     * @param array $smResponse SurveyMonkey API v3 response
     * @return bool|string
     */
    public function getEmailFromSmResponse($smResponse)
    {
        if (! isset($smResponse['pages'][0]['questions'][1]['answers'][0]['text'])) {
            return false;
        }

        return trim($smResponse['pages'][0]['questions'][1]['answers'][0]['text']);
    }

    /**
     * Advances a datetime string by one second
     *
     * Used to determine the earliest modified date for any new responses
     * based on the most recent modified date of old responses
     *
     * @param string $date Datetime string
     * @return false|string
     */
    public function advanceOneSecond($date)
    {
        $timestamp = strtotime($date);
        $timestamp += 1;

        return date('Y-m-d H:i:s', $timestamp);
    }
}
