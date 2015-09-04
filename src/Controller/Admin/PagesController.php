<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class PagesController extends AppController
{

    public function guide()
    {
        $this->set('titleForLayout', 'Admin Guide');
    }
}
