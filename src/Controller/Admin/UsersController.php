<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

class UsersController extends AppController
{
    public function index()
    {
        $cookieParentKey = 'AdminUsersIndex';
        $cookieKey = "$cookieParentKey.filter";

        // Remember selected filters
        $filter = $this->request->query('filter');
        if ($filter) {
            $this->Cookie->write($cookieKey, $filter);

        // Use remembered filter when no filter is manually specified
        } elseif ($this->Cookie->check($cookieKey)) {
            $filter = $this->Cookie->read($cookieKey);
        }

        // Apply filters
        if ($filter) {
            switch ($filter) {
                case 'client':
                case 'consultant':
                case 'admin':
                    $this->Paginator->settings['conditions']['Users.role'] = $filter;
                    break;
                default:
                    // No action
                    break;
            }
        }

        $this->set([
            'titleForLayout' => 'Users',
            'users' => $this->paginate(),
            'buttons' => [
                'all' => 'All Users',
                'client' => 'Clients',
                'consultant' => 'Consultants',
                'admin' => 'Admins'
            ]
        ]);
    }
}
