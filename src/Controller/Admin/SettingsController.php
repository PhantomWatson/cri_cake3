<?php
namespace App\Controller\Admin;

use App\Controller\AppController;

class SettingsController extends AppController
{
    /**
     * editCalculationSettings method
     *
     * @return void
     */
    public function editCalculationSettings()
    {
        $settingNames = ['intAlignmentAdjustment', 'intAlignmentThreshold'];
        $settings = $this->Settings->find('all')
            ->where(function ($exp, $q) use ($settingNames) {
                return $exp->in('name', $settingNames);
            })
            ->toArray();
        if ($this->request->is(['post', 'put'])) {
            foreach ($this->request->getData('settings') as $settingId => $settingValue) {
                $setting = $this->Settings->get($settingId);
                $setting = $this->Settings->patchEntity($setting, ['value' => $settingValue]);
                $errors = $setting->errors();
                if (empty($errors)) {
                    $this->Settings->save($setting);
                    $this->Flash->success($setting->name . ' updated');
                } else {
                    $this->Flash->error('There was an error updating ' . $setting->name);
                }
            }
        } else {
            foreach ($settings as $setting) {
                $this->request->data['settings'][$setting->id] = $setting->value;
            }
        }
        $this->set([
            'settings' => $settings,
            'titleForLayout' => 'Update Default Internal Alignment Calculation Settings'
        ]);
    }
}
