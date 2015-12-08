<?php
namespace App\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Behavior;

class SurveyMonkeyBehavior extends Behavior
{
    public function getSurveyMonkeyObject()
    {
        require_once(ROOT.DS.'vendor'.DS.'ascension'.DS.'php-surveymonkey'.DS.'src'.DS.'Ascension'.DS.'SurveyMonkey.php');
        $apiKey = Configure::read('survey_monkey_api_key');
        $accessToken = Configure::read('survey_monkey_api_access_token');
        return new \Ascension\SurveyMonkey($apiKey, $accessToken);
    }
}
