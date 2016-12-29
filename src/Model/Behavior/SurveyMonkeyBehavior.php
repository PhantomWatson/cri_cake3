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
}
