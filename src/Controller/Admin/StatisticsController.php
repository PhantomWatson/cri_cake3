<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class StatisticsController extends AppController
{
    public function import()
    {
        $this->Statistics->import();
    }
}
