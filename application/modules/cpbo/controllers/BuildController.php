<?php

/**
 * Description of BuildController
 *
 * @author Administrator
 */
class Cpbo_BuildController extends Nexva_Controller_Action_Cp_MasterBuildController {
    public function preDispatch() {
        parent::preDispatch();
    }

    public function init() {
        parent::init();
        $this->view->headScript()->appendFile('/cp/assets/js/uploadfile.js');
        $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/facebox/facebox.css');
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/facebox/facebox.js');
        
 
    }
    
    public function saveAction(){
        parent::saveAction();
    }
}

?>
