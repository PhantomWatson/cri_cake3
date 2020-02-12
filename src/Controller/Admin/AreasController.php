<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

class AreasController extends AppController
{
    /**
     * Runs Areas::importAreaData
     *
     * @return void
     */
    public function import()
    {
        $this->Areas->importAreaData();
    }
}
