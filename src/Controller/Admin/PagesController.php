<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class PagesController extends AppController
{

    public function guide()
    {
        $this->set('title_for_layout', 'Admin Guide');
    }
}
