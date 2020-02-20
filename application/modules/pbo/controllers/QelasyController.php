<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 5/22/14
 * Time: 1:05 PM
 * To change this template use File | Settings | File Templates.
 */

class Pbo_QelasyController extends Zend_Controller_Action
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

    /**
     * Lists Qelasy grades with it's details
     */
    public function qelasyGradesAction(){

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        //set the page title
        $this->view->title = "Qelasy Grades / User Types";

        if($this->_request->isPost())
        {
            $institute =  $this->_request->getPost('institute');

        } else {

            $institute =  trim($this->_request->institute_value);

        }

        //gets all active qelasy grades
        $qelasyGradeModel = new Pbo_Model_QelasyGrades();
        $grades = $qelasyGradeModel->getQelasyGradesAndInstitutes($institute);

        $this->view->institute_value = $institute;

        //setting the results through paginator
        $paginator = Zend_Paginator::factory($grades);
        $gradeCount = count($paginator);

        if($gradeCount > 0)
        {
            $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));
            $paginator->setItemCountPerPage(10);

            $this->view->grades = $paginator;
            unset($paginator);
        }

        $this->view->page = $this->_getParam('page');

        //get institutes
        $instituteModel = new Pbo_Model_QelasyInstitutes();
        $institutes = $instituteModel->fetchAll();
        $this->view->institutes = $institutes;

        //catches the success messages & assign it to the view
        $this->view->successMessages = $this->_helper->flashMessenger->setNamespace('success')->getMessages();

    }

    /**
     * Adds a new qelasy grade
     */
    public function addQelasyGradeAction(){

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        //set the view to be rendered
        $this->_helper->viewRenderer('qelasy-grade');

        //set the page title & action (this will be the action which form to be submit)
        $this->view->title = 'Create New Grade';
        $this->view->action = 'add-qelasy-grade';

        //assigning main categories to the view
        $categoryModel = new Pbo_Model_Categories();
        $categories = $categoryModel->getMainCategories();
        //$this->view->categories = $categories->toArray();
        $this->view->categories = $categories;
        $this->view->grade = null;

        $qelasyGradeModel = new Pbo_Model_QelasyGrades();
        $qelasyGradeCategoryModel = new Pbo_Model_QelasyGradeCategories();

        if($this->_request->isPost()){
            $params = $this->_getAllParams();

            $data = array
            (
                'name' => $params['grade-name'],
                'description' => $params['grade-description'],
                'status' => 1,
                'chap_id' => $chapId
            );

            //adds to the grade table
            $gradeId = $qelasyGradeModel->insert($data);

            //adds to the grade_category table
            $CategoryIds = $params['category'];
            if(!empty($CategoryIds)){
                foreach($CategoryIds as $CategoryId){
                    $qelasyGradeCategoryModel->addGradeCategory($gradeId,$CategoryId);
                }
            }

            $this->_helper->flashMessenger->setNamespace('success')->addMessage('Grade Successfully Added.');
            $this->_redirect('/qelasy/qelasy-grades');
        }
    }

    /**
     *
     */
    public function editQelasyGradeAction(){

        //catches the success messages & assign it to the view
        $this->view->successMessages = $this->_helper->flashMessenger->setNamespace('success')->getMessages();

        $this->_helper->viewRenderer('qelasy-grade');

        $id = $this->_request->getParam('id');
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        if(!$id){
            $this->_redirect('/qelasy/qelasy-grades');
        }

        $qelasyGradeModel = new Pbo_Model_QelasyGrades();
        $grade = $qelasyGradeModel->fetchRow($qelasyGradeModel->select()->where('id = ?',$id));

        $this->view->title = 'Edit Grade : '.utf8_decode($grade->name);
        $this->view->grade = $grade;

        $this->view->action = 'edit-qelasy-grade';

        $qelasyGradeCategoryModel = new Pbo_Model_QelasyGradeCategories();
        $categories = $qelasyGradeCategoryModel->getCategories($grade->id, $chapId , 1);

        //Zend_Debug::dump($categories);die();

        $this->view->categories = $categories;

        if($this->_request->isPost())
        {
            $params = $this->_getAllParams();

            //------------------------------------------Below codes no longer needs, as we sync the grade tables with qelasy db---------------//
            //saving grade details in qelasy_grade table
            /*$gradeData = array
            (
                'name' => $params['grade-name'],
                //'description' => $params['grade-description']
            );*/
            //$qelasyGradeModel->update($gradeData,'id = '.$params['id']);
            //----------------------------------------------------Ends removed codes----------------------------------------------------------//

            //saving grade-category details in qelasy_grade_categories table
            if (array_key_exists('category', $params)){
                $CategoryIds = $params['category'];
            }

            $categoryModel = new Pbo_Model_Categories();
            //$categoryData = $categoryModel->getMainCategories();
            $categoryData = $categoryModel->getAllCategories();

            $categories = array();
            foreach($categoryData as $category){
                $categories[] = (string)$category->id;
            }

            if(!empty($CategoryIds)){
                foreach($CategoryIds as $CategoryId){
                    $qelasyGradeCategoryModel->addGradeCategory($grade->id, $CategoryId);
                }

                $results = array_diff($categories, $CategoryIds);

                foreach($results as $result){
                    $qelasyGradeCategoryModel->removeGradeCategory($grade->id, $result);
                }

            } else {
                foreach($categories as $category){

                    $qelasyGradeCategoryModel->removeGradeCategory($grade->id, $category);

                }
                $this->_helper->flashMessenger->setNamespace('success')->addMessage('All Categories have removed from Grade.');
                $this->_redirect('/qelasy/edit-qelasy-grade/id/'.$grade->id);

            }

            //$this->_helper->flashMessenger->setNamespace('success')->addMessage('Grade Successfully Edited.');
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('Categories Successful assigned to Grade.');
            $this->_redirect('/qelasy/edit-qelasy-grade/id/'.$grade->id);
        }
    }

    public function deleteQelasyGradeAction(){

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $this->_request->getParam('id');
        $page = $this->_getParam('page');
        $data = array(
            'status' => 0
        );

        $qelasyGradeModel = new Pbo_Model_QelasyGrades();
        $qelasyGradeModel->update($data,'id = '.$id);

        $urlStr = '';
        $urlStr .= ($this->_getParam('page'))? '/page/'.$this->_getParam('page') : '' ;

        echo '/qelasy/qelasy-grades'.$urlStr;

        $this->_helper->flashMessenger->setNamespace('success')->addMessage('Grade Successfully Deleted.');
    }

    public function assignGradeForAppsAction(){

        $urlStr = '';
        $urlStr .= ($this->_getParam('page'))? '/page/'.$this->_getParam('page') : '' ;
        $urlStr .= ($this->_getParam('chk_filter'))? '/chk_filter/'.$this->_getParam('chk_filter') : 'all' ;
        $urlStr .= ($this->_getParam('search_key'))? '/search_key/'.$this->_getParam('search_key') : '' ;
        $urlStr .= ($this->_getParam('platform_filter'))? '/platform/'.$this->_getParam('platform_filter') : '' ;

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $params = $this->_getAllParams();
        $qelasyGradeAppsModel = new Pbo_Model_QelasyGradeApps();
        $grades = $qelasyGradeAppsModel->getQelasyGrades($params['appId'], $chapId);

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

        $this->_helper->flashMessenger->setNamespace('success')->addMessage('Grades Successfully Assigned for the App.');
        $this->_redirect('/app/index'.$urlStr);
    }

    public function getQelasyGradesAction(){

        $appId = $this->_request->getParam('appId');
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $qelasyGradeAppsModel = new Pbo_Model_QelasyGradeApps();
        $grades = $qelasyGradeAppsModel->getQelasyGrades($appId, $chapId);

        echo json_encode($grades->toArray());
        die();
    }

    public function editQelasyUserAction(){

        //catches the success messages & assign it to the view
        $this->view->successMessages = $this->_helper->flashMessenger->setNamespace('success')->getMessages();

        $userType = $this->_request->getParam('user_type');

        if(!$userType){
            $this->_redirect('/qelasy/qelasy-grades');
        }

        $qelasyUserTypeModel = new Pbo_Model_QelasyUserTypes();
        $userType = $qelasyUserTypeModel->fetchRow($qelasyUserTypeModel->select()->where('id = ?',$userType));

        $this->view->title = 'Edit User Type : '.ucwords($userType->type);
        $this->view->userType = $userType;

        $chapId = Zend_Auth::getInstance()->getIdentity()->id;

        $qelasyGradeCategoryModel = new Pbo_Model_QelasyGradeCategories();
        $categories = $qelasyGradeCategoryModel->getNonGradeCategories($chapId , $userType->id);

        $this->view->categories = $categories;

        if($this->_request->isPost())
        {
            $params = $this->_getAllParams();

            //saving grade-category details in qelasy_grade_categories table
            if (array_key_exists('category', $params)){
                $CategoryIds = $params['category'];
            }

            $categoryModel = new Pbo_Model_Categories();
            $categoryData = $categoryModel->getAllCategories();

            $categories = array();
            foreach($categoryData as $category){
                $categories[] = (string)$category->id;
            }

            if(!empty($CategoryIds)){
                foreach($CategoryIds as $CategoryId){
                    $qelasyGradeCategoryModel->addNonGradeCategory($userType->id, $CategoryId);
                }

                $results = array_diff($categories, $CategoryIds);

                foreach($results as $result){
                    $qelasyGradeCategoryModel->removeNonGradeCategory($userType->id, $result);
                }

            } else {
                foreach($categories as $category){

                    $qelasyGradeCategoryModel->removeNonGradeCategory($userType->id, $category);

                }
                $this->_helper->flashMessenger->setNamespace('success')->addMessage('All Categories have removed from this User Type.');
                $this->_redirect('/qelasy/edit-qelasy-user/user_type/'.$userType->id);

            }

            //$this->_helper->flashMessenger->setNamespace('success')->addMessage('Grade Successfully Edited.');
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('Categories Successful assigned to User Type.');
            $this->_redirect('/qelasy/edit-qelasy-user/user_type/'.$userType->id);

        }

    }

}