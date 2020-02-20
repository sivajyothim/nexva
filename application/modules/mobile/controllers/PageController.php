<?php
class Mobile_PageController extends Nexva_Controller_Action_Mobile_MasterController {
    
    public function init() {
        parent::init();
        $this->setCategories();
        $this->view->categories = $this->_categories;
        $this->view->enableCategories = true;
    }
    
    function viewAction() {
        $slug     = $this->_getParam('slug', null);
        $id       = $this->_getParam('id', null);
        $pageLang   = new Model_PageLanguages();
        if ($slug) {  
            $page       = $pageLang->getPageBySlug($slug);
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
        $this->view->page   = (object) $page;
    }
    
    /**
     * loads static content from from inside the current whitelabels folder
     */
    function staticAction() {
        $fileName       = $this->_getParam('id', '');
        $pathToFile     = APPLICATION_PATH . '/modules/mobile/whitelabels/docs/' . $this->themeMeta->WHITELABLE_THEME_NAME . '-' . $fileName . '.html';
        $content        = '';
        if (is_file($pathToFile)) {
            $content    = file_get_contents($pathToFile);
        }
        $page   = new stdClass();
        $page->content  = $content;
        $page->title    = '';
        $this->view->page = $page;
        $this->render('view');
    }
}