<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 5/12/14
 * Time: 12:19 PM
 * To change this template use File | Settings | File Templates.
 */

class Pbo_CategoryController extends Zend_Controller_Action
{
    public function preDispatch()
    {
        if( !Zend_Auth::getInstance()->hasIdentity() )
        {

            $skip_action_names = array ('login', 'register', 'forgotpassword', 'resetpassword', 'claim', 'impersonate');

            if (! in_array ( $this->getRequest()->getActionName (), $skip_action_names))
            {
                $requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
                $session = new Zend_Session_Namespace ( 'lastRequest' );
                $session->lastRequestUri = $requestUri;
                $session->lock ();
                $this->_redirect ( '/user/login' );
            }
        }

        $this->_helper->layout->setLayout('pbo/pbo');
    }

    public function indexAction(){

    }

    public function manageCategoryAction(){

        $this->view->title = "Category : Manage Categories";

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        $this->view->chapId = $chapId;

        $chapCategoryModel = new Pbo_Model_ChapCategories();
        $categories = $chapCategoryModel->getCategories($chapId);

        $this->view->categories = $categories;

        //Set the success messages if exists
        $this->view->successMessages = $this->_helper->flashMessenger->setNamespace('success')->getMessages();
        //Set the error messages if exists
        $this->view->errorMessages = $this->_helper->flashMessenger->setNamespace('error')->getMessages();

    }

    public function addChapCategoriesAction(){

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $messageNameSpace = '';

        if($this->_request->isPost())
        {
            $CategoryIds = $this->_request->category;
            $chapCategoryModel = new Pbo_Model_ChapCategories();

            $categoryModel = new Pbo_Model_Categories();
            //$categoryData = $categoryModel->getMainCategories();
            $categoryData = $categoryModel->getAllCategories();

            $categories = array();
            foreach($categoryData as $category){
                $categories[] = (string)$category->id;
            }

            if(!empty($CategoryIds)){
                foreach($CategoryIds as $CategoryId){
                    $messageNameSpace = $chapCategoryModel->addChapCategory($chapId,$CategoryId);
                }
                $results = array_diff($categories, $CategoryIds);
                foreach($results as $result){
                    $chapCategoryModel->removeChapCategory($chapId,$result);
                }
            } else {
                foreach($categories as $category){
                    $chapCategoryModel->removeChapCategory($chapId,$category);
                }
                $this->_helper->flashMessenger->setNamespace('success')->addMessage('All Categories Un-Assigned Successfully.');
                $this->_redirect( '/category/manage-category');
            }
        }
        $this->_helper->flashMessenger->setNamespace('success')->addMessage('Category Settings Successfully Changed.');
        /*if($messageNameSpace == 'success'){
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('Category Settings Successfully Changed.');
        }*/
        $this->_redirect( '/category/manage-category');
    }

    /*public function loadSubCategoryDivAction(){

        $catgoryModel = new Pbo_Model_Categories();
        $subCategories = $catgoryModel->getSubCategoriesForParentCategory(1);

        $echo = '';

        foreach($subCategories as $subCategory){
            $echo .= '<input name="category[]" class="category-chk" type="checkbox" value="'.$subCategory->id.'" />'.$subCategory->name;
        }
        $echo .= '<br/>';
        //echo '<input name="category[]" class="category-chk" type="checkbox" value="" />Hi there';
        echo $echo;
        die();
    }*/



##################################################Qelasy Fns###########################################################

    /**
     * Below fns has been moved to qelasyController
     */

    /*public function qelasyGradesAction(){

        $this->view->title = "Qelasy Grades";

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        $this->view->chapId = $chapId;

        $qelasyGradeModel = new Pbo_Model_QelasyGrades();
        $grades = $qelasyGradeModel->fetchAll(array("status"=>1));

        $paginator = Zend_Paginator::factory($grades);
        $gradeCount = count($paginator);

        if($gradeCount > 0)
        {
            $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));
            $paginator->setItemCountPerPage(10);

            $this->view->grades = $paginator;
            unset($paginator);
        }

        $this->view->successMessages = $this->_helper->flashMessenger->setNamespace('success')->getMessages();
    }

    public function addQelasyGradeAction(){

        $this->_helper->viewRenderer('qelasy-grade');
        $this->view->title = 'Create New Grade';
        $this->view->action = 'add-qelasy-grade';

        $qelasyGradeModel = new Pbo_Model_QelasyGrades();

        if($this->_request->isPost()){
            $params = $this->_getAllParams();

            $data = array
            (
                'name' => $params['grade-name'],
                'description' => $params['grade-description'],
                'status' => 1
            );
            $qelasyGradeModel->insert($data);

            $this->_helper->flashMessenger->setNamespace('success')->addMessage('Grade Successfully Added.');
            $this->_redirect('/category/qelasy-grades');
        }
    }

    public function editQelasyGradeAction(){

        $this->_helper->viewRenderer('qelasy-grade');

        $id = $this->_request->getParam('id');

        $qelasyGradeModel = new Pbo_Model_QelasyGrades();
        $grade = $qelasyGradeModel->fetchRow($qelasyGradeModel->select()->where('id = ?',$id));

        $this->view->title = "Edit Grade : $grade->name";
        $this->view->grade = $grade;

        $this->view->action = 'edit-qelasy-grade';

        if($this->_request->isPost())
        {
            $params = $this->_getAllParams();
            $data = array
            (
                'name' => $params['grade-name'],
                'description' => $params['grade-description']
            );
            $qelasyGradeModel->update($data,'id = '.$params['id']);

            //$this->view->gradeName = $params['grade-name'];
            //$this->view->gradeDescription = $params['grade-description'];
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('Grade Successfully Edited.');
            $this->_redirect('/category/qelasy-grades');
        }
    }

    public function deleteQelasyGradeAction(){

        $id = $this->_request->getParam('id');
        $data = array(
            'status' => 0
        );

        $qelasyGradeModel = new Pbo_Model_QelasyGrades();
        $qelasyGradeModel->update($data,'id = '.$id);

        $this->_helper->flashMessenger->setNamespace('success')->addMessage('Grade Successfully Deleted.');
        $this->_redirect('/category/qelasy-grades');
    }

    public function assignGradeForAppsAction(){

        $urlStr = '';
        $urlStr .= ($this->_getParam('page'))? '/page/'.$this->_getParam('page') : '' ;
        $urlStr .= ($this->_getParam('chk_filter'))? '/chk_filter/'.$this->_getParam('chk_filter') : 'all' ;
        $urlStr .= ($this->_getParam('search_key'))? '/search_key/'.$this->_getParam('search_key') : '' ;
        $urlStr .= ($this->_getParam('platform_filter'))? '/platform/'.$this->_getParam('platform_filter') : '' ;

        $params = $this->_getAllParams();
        $qelasyGradeAppsModel = new Pbo_Model_QelasyGradeApps();
        $grades = $qelasyGradeAppsModel->getQelasyGrades($params['appId']);

        $gradeIds = array();
        foreach($grades as $grade){
            $gradeIds[] = (string)$grade->id;
        }

        if($params['grades']){

            foreach($params['grades'] as $grade){
                $qelasyGradeAppsModel->assignQelasyGrade($params['appId'],$grade);
            }

            $results = array_diff($gradeIds, $params['grades']);

            foreach($results as $result){
                $qelasyGradeAppsModel->unAssignQelasyGrade($params['appId'],$result);
            }
        }

        foreach($params['grades'] as $grade){
            $qelasyGradeAppsModel->assignQelasyGrade($params['appId'],$grade);
        }


        if(!empty($CategoryIds)){
            foreach($CategoryIds as $CategoryId){
                $messageNameSpace = $chapCategoryModel->addChapCategory($chapId,$CategoryId);
            }
            $results = array_diff($categories, $CategoryIds);
            foreach($results as $result){
                $chapCategoryModel->removeChapCategory($chapId,$result);
            }
        }

        $this->_helper->flashMessenger->setNamespace('success')->addMessage('Grades Successfully Assigned for the App.');
        $this->_redirect('/app/index'.$urlStr);
    }*/

    /*public function getQelasyGradesAction(){

        $appId = $this->_request->getParam('appId');

        $qelasyGradeAppsModel = new Pbo_Model_QelasyGradeApps();
        $grades = $qelasyGradeAppsModel->getQelasyGrades($appId);

        echo json_encode($grades->toArray());
        die();
    }*/

}