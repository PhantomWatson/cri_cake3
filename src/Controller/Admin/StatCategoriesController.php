<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

class StatCategoriesController extends AppController
{
    /**
     * Import method
     *
     * @return void
     */
    public function import()
    {
        $this->StatCategories->import();
    }
}
