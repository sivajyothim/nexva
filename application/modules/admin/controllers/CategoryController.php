<?php
class Admin_CategoryController extends Zend_Controller_Action{
    public function preDispatch(){
       if( !Zend_Auth::getInstance()->hasIdentity() ) {

            if($this->_request->getActionName() != "login") {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();

            }
            if( $this->_request->getActionName() != "login" )
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/login');
        }
    }

    function indexAction(){

        $categoryModel  =   new Model_Category();
        $this->view->formattedCategories    =   $this->getCategoriesAsParentChild();
        
        
    }

    function createAction(){
        $categoryModel  =   new Model_Category();
        $categories =   $categoryModel->select()
                        ->from('categories',array("id","name"));
                        
        $this->view->parentCategory =   $categoryModel->fetchAll($categories)->toArray();
        

    }
    function editAction(){
        $categoryModel  =   new Model_Category();
        $this->view->category   =   current($categoryModel->find($this->_request->id)->toArray());
        
        $this->createAction();
        $this->render('/create');

    }

    function deleteAction(){

        $categoryModel  =   new Model_Category();
        $categoryModel->delete('id='.$this->_request->id);
        $this->view->message    =   "Category , attached with id - {$this->_request->id} is deleted.";
        $this->indexAction();
        $this->render('/index');
    }

    function saveAction(){

            if($this->_request->id == ''){
                //create new

                $categoryModel  =   new Model_Category();
                $categoryModel->insert(array(
                   "id" => NULL,
                    "parent_id" => $this->_request->parentCategoryId,
                    "name"  => $this->_request->categoryName,
                    "status" => $this->_request->categoryStatus

                ));

                $this->view->message    =   "Category {$this->_request->categoryName} is created.";
                $this->createAction();
                $this->render('/create');

            }else{
       
                //update existing
                $categoryModel  =   new Model_Category();
                $categoryModel->update(array(
                   
                    "parent_id" => $this->_request->parentCategoryId,
                    "name"  => $this->_request->categoryName,
                    "status" => $this->_request->categoryStatus

                ),"id = ".$this->_request->id);

                $this->view->message    =   "Category {$this->_request->categoryName} is updated.";
                $this->editAction();
                
               

            }
    }


    function getCategoriesAsParentChild(){
        $categoryModel  = new Model_Category();
        $parentCategories   =   $categoryModel->select()
                                ->from('categories',array("id","name","status"))
                                ->where('parent_id = 0');
        $parentCategories   =   $categoryModel->fetchAll($parentCategories)->toArray();

        foreach($parentCategories as $parentCategory){

            
            $categoryModelnew   =   new Model_Category();
            $attachedCategories =   $categoryModelnew->select()
                                    ->from('categories',array("id","name","status"))
                                    ->where('parent_id = '.$parentCategory['id']);
                                    
            $categories         =   $categoryModelnew->fetchAll($attachedCategories)->toArray();           
            $formattedCategory[$parentCategory['id']][]=$parentCategory['name'];
          
                foreach($categories as $cat){
                    
                    $formattedCategory[$parentCategory['id']][$cat['id']]=$cat['name'];

                }
               
        }
        return  $formattedCategory;
    }
}

?>