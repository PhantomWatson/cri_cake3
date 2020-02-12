<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Cache\Cache;

class PagesController extends AppController
{
    /**
     * Method for /admin/pages/guide
     *
     * @return void
     */
    public function guide()
    {
        $this->set('titleForLayout', 'Admin Guide');
    }

    /**
     * Clears all caches
     *
     * @return \Cake\Http\Response
     */
    public function clearCache()
    {
        Cache::clear(false, 'default');
        Cache::clear(false, '_cake_core_');
        Cache::clear(false, '_cake_model_');
        Cache::clear(false, 'survey_urls');
        $this->Flash->success('Cache cleared');

        return $this->redirect('/');
    }
}
