<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 7/2/14
 * Time: 3:20 PM
 * To change this template use File | Settings | File Templates.
 */

error_reporting(E_ALL);
set_time_limit(0);
include_once ("../application/BootstrapCli.php");

//$this->forward
//echo 'Hi there';die();

$databases = array(
    array(                              //master db credentials   54.86.141.106 3306
        'type' => "mysqli",
        'server'=> "54.86.141.106 3306",
        //'server'=> "192.168.0.116",
        'name'=>"QelasyLive",
        //'name'=>"nexva_v2_dev",
        'username'=>"root",
        'password'=>"user@123"
    ),
    array(                              //slave db credentials
        'type' => "mysqli",
        'server'=> "localhost",
        //'name'=>"nexva_v2_production",
        'name'=>"nexva_v2_production",
        'username'=>"developer",
        //'username'=>"root",
        'password'=>"p16H:s1a?/:;16-"
        //'password'=>""
    )
);

$options = array(
    'type'  =>  array(
        'data' => 1
    ),
    'database'  =>  array(
        array(                  // master db tables (reading tables)
            //'table' => array('Grade'=>1)
            'table' => array('Grade'=>1,'Institute'=>1)
        ),
        array(                  // slave db tables (compare with master tables & supposed to be insert/update)
            //'table' =>  array('qelasy_grades'=>1)
            'table' =>  array('qelasy_grades'=>1, 'qelasy_institutes'=>1)
        )
    )
);

$comparison = new MyDiff_Comparison;
foreach($databases AS $database)
{
    $database = new MyDiff_Database($database);
    $database->connect();
    $comparison->addDatabase($database);
}

foreach($comparison->databases AS $i => $database)
{
    $database->useTables(array_keys($options['database'][$i]['table']));
    $database->connect();
}

if(isset($options['type']['data']))
{
    $comparison->data();

    // Build a list of rows that have changed
    $data = array();
    $tables = array($comparison->databases[0]->getTables(), $comparison->databases[1]->getTables());

    if(!$tables[0]["Grade"]->hasDiffs('MyDiff_Diff_Table_New')){

        $rows = array($tables[0]["Grade"]->getRows(), (isset($tables[1]["qelasy_grades"])? $tables[1]["qelasy_grades"]->getRows() : array()));

        if(!empty($rows[0]) && !empty($rows[1])){

            $GradeArray = array();
            foreach($rows[0] as &$row){
                //$row->data['name'] = htmlentities($row->data['name'], ENT_QUOTES);
                $row->data['name'] = utf8_encode($row->data['name']);
                //$row->data['name'] = mb_convert_encoding($row->data['name'],"HTML-ENTITIES","UTF-8");
                $GradeArray[] = $row->data;
            }

            $qelasy_gradesArray = array();
            foreach($rows[1] AS &$row){
                $qelasy_gradesArray[] = $row->data;
            }

            $qelasyGradeModel = new Pbo_Model_QelasyGrades();

            $i = 0;
            for( $i ; $i < count($GradeArray); $i++ ){

                if( $i < count($qelasy_gradesArray) ){
                    $result = array_diff_assoc($GradeArray[$i], $qelasy_gradesArray[$i]);
                    if(!empty($result)){
                        $qelasyGradeModel->update($GradeArray[$i],'id ='.$GradeArray[$i]['id']);
                        //Zend_Debug::dump($GradeArray[$i]);
                    }
                } else {
                    $qelasyGradeModel->insert($GradeArray[$i]);
                    //Zend_Debug::dump($GradeArray[$i]);
                }

            }

        }

    }

    if(!$tables[0]["Institute"]->hasDiffs('MyDiff_Diff_Table_New')){

        $rows = array($tables[0]["Institute"]->getRows(), (isset($tables[1]["qelasy_institutes"])? $tables[1]["qelasy_institutes"]->getRows() : array()));

        if(!empty($rows[0]) && !empty($rows[1])){

            $InstituteArray = array();
            foreach($rows[0] as &$row){
                $row->data['name'] = utf8_encode($row->data['name']);
                $InstituteArray[] = $row->data;
            }

            $qelasy_instituteArray = array();
            foreach($rows[1] AS &$row){
                $qelasy_instituteArray[] = $row->data;
            }

            $qelasyInstituteModel = new Pbo_Model_QelasyInstitutes();

            $i = 0;
            for( $i ; $i < count($InstituteArray); $i++ ){

                if( $i < count($qelasy_instituteArray) ){
                    $result = array_diff_assoc($InstituteArray[$i], $qelasy_instituteArray[$i]);
                    if(!empty($result)){
                        $qelasyInstituteModel->update($InstituteArray[$i],'id ='.$InstituteArray[$i]['id']);
                        //echo 'updated'.PHP_EOL;
                        //Zend_Debug::dump($InstituteArray[$i]);
                    }
                } else {
                    $qelasyInstituteModel->insert($InstituteArray[$i]);
                    //echo 'inserted'.PHP_EOL;
                    //Zend_Debug::dump($InstituteArray[$i]);
                }

            }

        }

    }
}