<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class StatisticsController extends AppController
{
    /**
     * Import method
     *
     * @return void
     */
    public function import()
    {
        $this->Statistics->import();
    }
}
