<?php
/**
 * Class for CPBO module that plugs into admin page 
 *
 * @copyright neXva.com
 * @author John Pereira
 * @date Jan 31, 2011
 */
class Cpbo_PageController extends Nexva_Controller_Action_Cp_MasterController {
    
    function preDispatch() {
        //clearing the login check that is defined in the master controller
    }
    
    /**
     * 
     * Special method to pick up promo stuff for the login area
     * @param $slug
     */
    function loginAction() {
        //see if we can load up the page with the matching slug
        
        $slug           = $this->_request->getParam('slug');
        //echo $slug;die();
        if($slug == 'OpenMobileAppMall')
        {
            $slug = 'OpenMobile'.' '.'AppMall';
        }
        if($slug == 'localization')
        {
            $slug = ucfirst($slug);
        }
        $modelPage      = new Model_PageLanguages();
        $page           = $modelPage->getPageBySlug($slug);

        if (!$page)
        {
            $this->_redirect('/user/login');
        }
        $this->view->currentTab     = $slug;
        $this->view->page = $page; 
        $this->_helper->layout->setLayout ( 'cp_login' );
    }
    
    function viewAction() {
        $slug     = $this->_getParam('slug', null);
        $id       = $this->_getParam('id', null);
        $languageId=1;
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        if(in_array($chapId, array('585474','585480')) && in_array($slug, array('nexpush','nexlinker','neXpublisher','nexpayer'))){
            $languageId=2;
        }
        try{
        $pageLang   = new Model_PageLanguages();
        if ($slug) {  
            $page       = $pageLang->getPageBySlug($slug,$languageId);
            if ($page == null) {
                throw new Exception('Page not found');
            }
        } else if (is_numeric($id)) {
            $page       = $pageLang->getPageById($id);
            if ($page == null) {
                throw new Exception('Page not found');
            }
        } else {
            throw new Exception('Page not found');
        }
        } catch (Exception $e){
            var_dump($e->getMessage());die();
        }
        print_r($page);exit;
        $this->view->page   = (object) $page;
    }

    function chapAction(){

        if (Zend_Auth::getInstance()->hasIdentity() == false) {
            $this->_redirect('/user/login');
        }

        //channel details
        $userModel = new Cpbo_Model_User();
        $channelDetails = $userModel->getAllChannelDetails();

        $this->view->channelDetails = $channelDetails;
    }

    /**
     *
     */
    function ycoinsAction(){

        $session = new Zend_Session_Namespace('chap');

        if('ycoins' != $session->chap->username){
            $this->_redirect('/');
        }
        $this->view->title = 'Android Library';

        $productId = $this->_getParam('product_id', null);

        $userId = Zend_Auth::getInstance()->getIdentity()->id;
        $productModel = new Cpbo_Model_Product();
        $inappProducts = $productModel->fetchAll('user_id = '.$userId.' AND inapp = 1 AND deleted = 0');

        $this->view->hasApps = false;
        if(count($inappProducts)){
            $this->view->hasApps = true;
            $this->view->inappProducts = $inappProducts;
        }

    }

    /*
     *
     */
    function downloadLibraryAction(){

        $filename = $this->_request->getParam('name');

        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        switch($filename){
            case 'library':
                $file = $_SERVER['DOCUMENT_ROOT'].'/ycoins/CentiliLib.zip';
                $name = "CentiliLib.zip";
                $fp = fopen($file, 'rb');
                header("Pragma: public");
                header("Content-Description: File Transfer");
                header("Content-type : application/octet-stream"); //@todo download prompt doesn't shows it as zip
                header("Content-Length: " . filesize($file));
                header('Content-Disposition: attachment; filename = '.$name);
                header("Content-Transfer-Encoding: binary");
                fpassthru($fp);
                fclose($fp);
                die();
                break;
            case 'doc' :
                $file = $_SERVER['DOCUMENT_ROOT'].'/ycoins/android-instructions.pdf';
                $name = "android-instructions.pdf";
                $fp = fopen($file, 'rb');
                header("Content-type:application/pdf");
                header("Content-Length: " . filesize($file));
                header('Content-Disposition: attachment; filename = '.$name);
                header("Content-Transfer-Encoding: binary");
                fpassthru($fp);
                fclose($fp);
                die();
                break;
        }
    }
    
    public function termsAction(){
        $this->_helper->layout->setLayout ( 'cp_login' );
    }
}