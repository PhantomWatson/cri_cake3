<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class StatCategoriesController extends AppController
{
    public function index()
    {
        $this->set('statCategories', $this->paginate());
    }

    public function view($id = null)
    {
        $this->set('statCategory', $this->StatCategories->get($id));
    }

    public function add()
    {
        if ($this->request->is('post')) {
            $statCategory = $this->StatCategories->newEntity($this->request->data);
            if ($this->StatCategories->save($statCategory)) {
                $this->Flash->success('Stat category added');
                return $this->redirect(['action' => 'index']);
            }
        }
    }
}
