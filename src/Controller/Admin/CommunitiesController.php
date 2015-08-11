<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class CommunitiesController extends AppController
{
    public function index()
    {
        if (isset($_GET['search'])) {
            $this->paginate['conditions']['Community.name LIKE'] = '%'.$_GET['search'].'%';
        } else {
            $this->adminIndexFilter();
        }
        $this->cookieSort('AdminCommunityIndex');
        $this->adminIndexSetupPagination();
        $this->adminIndexSetupFilterButtons();
        $this->set(array(
            'communities' => $this->paginate(),
            'title_for_layout' => 'Indiana Communities'
        ));
    }
}
