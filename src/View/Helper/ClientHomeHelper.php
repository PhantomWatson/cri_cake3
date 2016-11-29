<?php
namespace App\View\Helper;

use Cake\View\Helper;

class ClientHomeHelper extends Helper
{
    /**
     * Returns a Bootstrap glyphicon indicating success or failure
     *
     * @param bool $bool Boolean for success or failure
     * @return string
     */
    public function glyphicon($bool) {
        $class = $bool ? 'ok' : 'remove';
        return '<span class="glyphicon glyphicon-'.$class.'"></span>';
    }

    /**
     * Returns a <tbody> string with the appropriate class
     *
     * @param $tbodyStep Step that this <tbody> contains
     * @param $currentStep Step that this community is currently at
     * @return string
     */
    public function tbodyForStep($tbodyStep, $currentStep) {
        if ($tbodyStep > floor($currentStep)) {
            return '<tbody class="future">';
        }
        if ($tbodyStep < floor($currentStep)) {
            return '<tbody class="past">';
        }
        return '<tbody class="current">';
    }
}