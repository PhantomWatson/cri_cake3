<?php
namespace App\ToDo;

use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

class ToDo
{
    public $communitiesTable;

    public function __construct()
    {
        $this->communitiesTable = TableRegistry::get('Communities');
    }

    /**
     * Returns an array of ['class' => '...', 'msg' => '...']
     * representing the current to-do item for the selected community
     *
     * @param int $communityId Community ID
     * @return array
     */
    public function getToDo($communityId)
    {
        if ($this->readyForClientAssigned($communityId)) {
            $url = Router::url([
                'prefix' => 'admin',
                'controller' => 'Communities',
                'action' => 'clients',
                $communityId
            ]);
            return [
                'class' => 'ready',
                'msg' => 'Ready to <a href="' . $url . '">assign a client</a>'
            ];
        }

        return [
            'class' => 'incomplete',
            'msg' => '(criteria tests incomplete)'
        ];
    }

    /**
     * Returns whether or not the community needs a client assigned
     *
     * @param int $communityId Community ID
     * @return bool
     */
    private function readyForClientAssigned($communityId)
    {
        $clients = $this->communitiesTable->getClients($communityId);

        return empty($clients);
    }
}
