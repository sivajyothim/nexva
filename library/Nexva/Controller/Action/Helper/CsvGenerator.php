<?php
class Nexva_Controller_Action_Helper_CsvGenerator extends Zend_Controller_Action_Helper_Abstract
{
    public function preDispatch(){


    }
    
    function gnerate($path,$fields,$delimiter=',',$titles=''){

        if(''==$delimiter){
            $delimiter=',';
        }

        if($fields instanceof Zend_Db_Table_Rowset ){
        $fields = $fields->toArray();

        }else{
            throw  new Zend_Exception('Field should be an instance of Zend db table rowset');
            
        }
     
        $filePointer = fopen($path, "w+");
       
        if(false !== $filePointer){
            if(isset($titles) and is_array($titles)){
                fputcsv($filePointer,$titles, $delimiter);
            }
            
            foreach($fields as $field){
                //$field = array($field);               
              fputcsv($filePointer,$field, $delimiter);
            }
           
            fclose($filePointer);
            return $path;

        }else{
            throw new Exception("Given path is not a valid resource");
        }
    }
}



?>