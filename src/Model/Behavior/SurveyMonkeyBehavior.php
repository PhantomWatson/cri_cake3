<?php
namespace App\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Behavior;

class SurveyMonkeyBehavior extends Behavior
{
    public function getSurveyMonkeyObject() {
        require_once(ROOT.DS.'vendor'.DS.'ascension'.DS.'php-surveymonkey'.DS.'src'.DS.'Ascension'.DS.'SurveyMonkey.php');
        $api_key = Configure::read('survey_monkey_api_key');
        $access_token = Configure::read('survey_monkey_api_access_token');
        return new \Ascension\SurveyMonkey($api_key, $access_token);
    }
}
