<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class PagesController extends AppController
{

    /**
     * Method for /admin/pages/guide
     *
     * @return void
     */
    public function guide()
    {
        $this->set('titleForLayout', 'Admin Guide');
    }
}
