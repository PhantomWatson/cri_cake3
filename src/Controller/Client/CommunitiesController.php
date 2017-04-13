<?php
namespace App\Controller\Client;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception\NotFoundException;

class CommunitiesController extends AppController
{
    /**
     * Client home page
     *
     * @return \App\Controller\Response|void
     */
    public function index()
    {
        $this->viewBuilder()->helpers(['ClientHome']);

        $userId = $this->Auth->user('id');
        $communityId = $this->Communities->getClientCommunityId($userId);
        if ($communityId) {
            $this->loadComponent('ClientHome');
            if ($this->ClientHome->prepareClientHome($communityId)) {
                return;
            }
        }

        $this->set('titleForLayout', 'CRI Account Not Yet Ready For Use');

        return $this->render('notready');
    }

    /**
     * Method for /client/communities/reactivate
     *
     * @return void
     */
    public function reactivate()
    {
        $clientId = $this->Auth->user('id');
        $communityId = $this->Communities->getClientCommunityId($clientId);

        if (! $communityId) {
            $msg = 'Your client account is not associated with any communities.';
            throw new NotFoundException($msg);
        }

        $community = $this->Communities->get($communityId);
        $currentlyActive = $community->active;
        if ($this->request->is('put')) {
            $community = $this->Communities->patchEntity($community, [
                'active' => true
            ]);

            $adminEmail = Configure::read('admin_email');
            $errorMsg = 'There was an error resuming your participation in CRI. ' .
                'Please contact <a href="mailto:' . $adminEmail . '">' . $adminEmail . '</a> for assistance.';

            if ($community->errors()) {
                $this->Flash->error($errorMsg);
            } elseif ($this->Communities->save($community)) {
                $currentlyActive = true;

                // Dispatch event
                $eventName = 'Model.Community.afterActivate';
                $event = new Event($eventName, $this, ['meta' => [
                    'communityId' => $communityId
                ]]);
                $this->eventManager()->dispatch($event);
            } else {
                $this->Flash->error($errorMsg);
            }
        }

        $this->set([
            'community' => $community,
            'currentlyActive' => $currentlyActive,
            'titleForLayout' => 'Reactivate Account'
        ]);
    }
}
