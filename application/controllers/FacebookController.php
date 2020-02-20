<?php

/**
 * Controller for Facebook app. This was never ported into v2 and for now, we simply show the users who installed it with a coming soon page.
 *
 * 
 */
class Default_FacebookController extends Nexva_Controller_Action_Web_MasterController {

    public function indexAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        echo "
            <center> <strong>Application Temporarily Unavailable</strong>  <br /> <br />

            neXva's Facebook Application is undergoing maintenance and is unavailable at this time. <br /> <br />
            
            <a href='http://nexva.com'> Go to neXva.com </a>

            </center>
           
        ";




        

    }
    
}
?>
