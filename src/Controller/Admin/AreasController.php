<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class AreasController extends AppController
{
    public function import()
    {
        $this->Areas->importAreaData();
    }
}
