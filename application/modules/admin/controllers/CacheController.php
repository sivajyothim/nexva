<?php
/**
 * 
 * Class that allows you to manipulate the caching running on the site 
 * @author Administrator
 *
 */
class Admin_CacheController extends Nexva_Controller_Action_Admin_MasterController {

    /**
     * 
     * Lets you clear a few important cache objects
     */
    function indexAction() {
        $message    = '';
        if ($this->_request->isPost()) {
            $id     = $this->_request->getParam('id', null);
            $mode   = $this->_request->getParam('mode', 'product');
            $cacheUtil  = new Model_CacheUtil();
            switch ($mode) {
                case 'product':
                    $cacheUtil->clearProduct($id);
                    break;
                    
                case 'user':
                    $cacheUtil->clearUser($id);
                    break;
            }
            $message    = 'Item removed from cache';
        }
        
        $this->view->message    = $message;
    }
    
    function keyAction() {
        if ($this->_request->isPost()) {
            $key        = $this->_request->getParam('id', '');
            $cache      = new Model_CacheUtil();
            $cache->clear(array($key));
        }
        $this->_helper->FlashMessenger->addMessage('Item removed from cache');
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'cache/');        
    }
    
    
    function bulkAction() {
        if ($this->_request->isPost()) {
            $type       = $this->_request->getParam('mode', 'product');
            $cache      = new Model_CacheUtil();
            $cache->clearType($type);
        }
        $this->_helper->FlashMessenger->addMessage('Item removed from cache');
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'cache/');
    }
    
    function keysAction() {
        $cache      = Zend_Registry::get('cache');
        $key        = 'SITEKEYS';
        $keys       = $cache->get($key);
        Zend_Debug::dump($keys); die();
    }
}