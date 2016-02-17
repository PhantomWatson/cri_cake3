<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class StatCategoriesController extends AppController
{
    public function import()
    {
        $this->StatCategories->import();
    }
}
