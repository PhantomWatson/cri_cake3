<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * @property \App\Model\Table\StatisticsTable $Statistics
 */
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

    /**
     * Import-grouped method
     *
     * @return void
     */
    public function importGrouped()
    {
        $this->Statistics->importGrouped();
    }
}
