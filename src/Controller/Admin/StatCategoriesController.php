<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class StatCategoriesController extends AppController
{
    public function index() {
        $this->set('statCategories', $this->paginate());
    }
}
