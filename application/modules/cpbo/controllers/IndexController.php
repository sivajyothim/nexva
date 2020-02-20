<?php

class Cpbo_IndexController extends Nexva_Controller_Action_Cp_MasterController {

  public function preDispatch() {
      
     

    $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/facebox/facebox.css');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/facebox/facebox.js');
    $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/cp/assets/js/cp.js');
    $requestpage = new Zend_Session_Namespace('request');
    $requestpage->page = $_SERVER['REQUEST_URI'];

    if (Zend_Auth::getInstance()->hasIdentity() == false) {

      $this->_redirect(CP_PROJECT_BASEPATH.'user/login');
    }
  }
    public function init ()	{
        
        

    
    }

  public function indexAction() {
      


        /*$userId = Zend_Auth::getInstance()->getIdentity()->id;
        $db = Zend_Registry::get('db');
        $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users');

        $authDbAdapter->setIdentityColumn('id');
        $authDbAdapter->setIdentity($userId);
        $authDbAdapter->setCredentialColumn('agreement_sign');
        $authDbAdapter->setCredential('1');

        $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);
        if($result->isValid()){
            //echo 'valid';die();
        }else{
            echo 'in-valid';die();
        }*/

      //check whether user has agreed to updated user-agreement & if not redirect to confirm page
      $userId = Zend_Auth::getInstance()->getIdentity()->id;
      $userModel = new Cpbo_Model_User();
      $userDetails = $userModel->getUserAgreementConfirm($userId);
      //echo $userDetails->agreement_sign;die();
      if( $userDetails->agreement_sign != 1 ){
          $this->_redirect(CP_PROJECT_BASEPATH.'agreement/confirm-agreement');
      }

		$downloads      = new Nexva_Analytics_ProductDownload();
		$views          = new Nexva_Analytics_ProductView(); 
		
		// Get the stats for total download/views for graph 
		$downloadsByDate    = $downloads->getAppDownloadsByDate($this->_getCpId());
		$viewByDate         = $views->getAppViewsByDate($this->_getCpId());
		
		$this->view->appDownloadDateJson    = json_encode($downloadsByDate); 
        $this->view->appViewDateJson        = json_encode($viewByDate);
        
        // Get the stats for total download/views trends
        $this->view->viewTrends	= $views->getTotalViewTrends($this->_getCpId());
        $this->view->downloadTrends	= $downloads->getTotaldownloadTrends($this->_getCpId());

      
        $this->view->blogItems = $this->__getFeed(); //getting the feed from the blog
        $modelPage     = new Model_PageLanguages();
        $page          = $modelPage->getPageBySlug('CP_FEATURED_VIDEO');
        $this->view->featuredVid   = isset($page['content'])  ? $page['content'] : ''; 

        $statisticModel    =   new Model_StatisticDevice();
        $products = new Model_Product();
        
        $last_ten = $products->select();
        
        $last_ten->setIntegrityCheck(false)
            ->from("products")
            ->where("user_id=?", Zend_Auth::getInstance()->getIdentity()->id)
            ->where("deleted != ?", 1)
            ->limit(10)
            ->order("products.id desc")
            ->group("products.id")
            ->query();
        $this->view->products = $products->fetchAll($last_ten);
        
        $rowCount = count($this->view->products);
        if (0 == $rowCount) {
          $this->view->show_empty_msg = true;
        }
        $this->view->title = "Dashboard";
        
        $userMeta   = new Model_UserMeta();
        $userMeta->setEntityId($this->_getCpId());
        $this->view->nexpagerState  = $userMeta->ACTIVE_NEXPAGE;
 
  }


  public function helpAction(){
    $this->_helper->getHelper('layout')->disableLayout();
    
  }

/* 
 * DELETE AFTER THE NEXT COMMIT _jp (9 june)
 * 
 *  public function annoucementsAction() {

    $announcementsModel = new Cpbo_Model_Annoucement();
    $announcements = $announcementsModel->getUnreadAnnoucements(Zend_Auth::getInstance()->getIdentity()->id);

    $userMeta = new Model_UserMeta();
    $userMeta->setEntityId(Zend_Auth::getInstance()->getIdentity()->id);

    

    if( 0 == count($announcements) ) $this->_redirect('/');

    //mark them all 'read'
    $userAnnouncementsModel = new Model_UserAnnouncement();

    foreach($announcements as $announcement) {
        $userAnnouncementsModel->markAnnoucementAsUnRead($announcement->id, Zend_Auth::getInstance()->getIdentity()->id);
    }
    
    $this->view->announcements = $announcements;
    $this->view->firstName = $userMeta->FIRST_NAME;
  }*/
  
    private function __getFeed() {
        
        $cache      = Zend_Registry::get('cache');
        $key        = 'CP_INDEX_FEED';
        if (($items = $cache->get($key)) !== false) {
            return $items;
        }
        
        try {
            $channel    = new Zend_Feed_Rss('http://www.mobilopen.org/feed');
            $items      = array(); 
            foreach ($channel as $item) {
                $simpleItem = new stdClass();
                $simpleItem->title      = html_entity_decode($item->title()); 
                $simpleItem->link       = $item->link();
                $simpleItem->summary    = mb_substr(strip_tags($item->description()), 0, 400);
                $simpleItem->date       = $item->pubDate();
                $items[]    = $simpleItem;
            }
            
            $cache->set($items, $key, 86400);
        } catch (Exception $ex) {
            $items      = array();
            Zend_Registry::get('logger')->err("Couldn't load feed from mobileopen");
        }
        return $items;
  }

}

