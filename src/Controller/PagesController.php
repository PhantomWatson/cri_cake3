<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow();
        $this->Auth->deny('admin_guide');
    }

    public function home()
    {
        $this->set('title_for_layout', '');
    }

    public function glossary()
    {
        $this->set('title_for_layout', 'Glossary');
    }

    public function faq_community()
    {
        $this->set('title_for_layout', 'Frequently Asked Questions for Communities');
    }

    public function faq_consultants()
    {
        $this->set('title_for_layout', 'Frequently Asked Questions for CRI Consultants');
    }

    public function fasttrack()
    {
        $this->set('title_for_layout', 'CRI Fast Track');
    }

    public function credits()
    {
        $this->set('title_for_layout', 'Credits and Sources');
    }

    public function enroll()
    {
        $this->redirect('https://www.surveymonkey.com/s/XFT6CSZ');
    }

    public function admin_guide()
    {
        $this->set('title_for_layout', 'Admin Guide');
    }
}
