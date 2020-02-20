<?php
class Chap_SettingsController extends Nexva_Controller_Action_Chap_MasterController {
    
    public function indexAction() {
        $settingsModel  = new Chap_Model_Setting();
        $settings       = $settingsModel->getSettings();

        $themeMetaModel = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($this->_getUserId());

        $savedSettings  = ($themeMetaModel->WHITELABLE_SETTINGS) ? json_decode($themeMetaModel->WHITELABLE_SETTINGS) : new stdClass();

        $this->view->savedSettings  = $savedSettings;
        $this->view->settings       = $settings;
    }
    
    public function saveAction() {
        $settingsModel  = new Chap_Model_Setting();
        $settings       = $settingsModel->getSettings();
        $settingObj     = new stdClass();
        foreach ($settings as $setting => $opts) {
            $settingObj->{$setting} = $this->_getParam($setting, null);
        }

        $themeMetaModel = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($this->_getUserId());
        $themeMetaModel->WHITELABLE_SETTINGS    = json_encode($settingObj);
        $this->_redirect('/settings');
    }
    
    public function cpHomeCustomizeAction()
    {
        $themeMetaModel = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($this->_getUserId());
            
        if($this->_request->isPost())
        {
            $description = $this->_getParam('description');
            
            
            $themeMetaModel->CP_HOME_DESCRIPTION    = $description;
            $this->_redirect('/settings/cp-home-customize');
        }
        
        
        $this->view->description  = ($themeMetaModel->CP_HOME_DESCRIPTION) ? $themeMetaModel->CP_HOME_DESCRIPTION : '';
        
    }
    
    
}