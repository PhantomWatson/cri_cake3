<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\Setting;
use Cake\Database\Expression\QueryExpression;

class SettingsController extends AppController
{
    /**
     * editCalculationSettings method
     *
     * @return void
     */
    public function editCalculationSettings()
    {
        if ($this->request->is(['post', 'put'])) {
            $settings = [];
            foreach ($this->request->getData('settings') as $settingId => $settingValue) {
                /** @var Setting $setting */
                $setting = $this->Settings->get($settingId);
                $setting = $this->Settings->patchEntity($setting, ['value' => $settingValue]);
                $errors = $setting->getErrors();
                if (empty($errors)) {
                    $this->Settings->save($setting);
                    $this->Flash->success($setting->name . ' updated');
                } else {
                    $this->Flash->error('There was an error updating ' . $setting->name);
                }
                $settings[] = $setting;
            }
        } else {
            $settingNames = ['intAlignmentAdjustment', 'intAlignmentThreshold'];
            $settings = $this->Settings->find('all')
                ->where(function ($exp) use ($settingNames) {
                    /** @var QueryExpression $exp */

                    return $exp->in('name', $settingNames);
                })
                ->toArray();
        }
        $this->set([
            'settings' => $settings,
            'titleForLayout' => 'Update Default Internal Alignment Calculation Settings'
        ]);
    }
}
