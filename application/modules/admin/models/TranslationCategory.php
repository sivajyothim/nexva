<?php
class Admin_Model_TranslationCategory extends Model_Language
{
    protected $_name = 'translations_categories';
    protected $_id = 'id';

    // add translation details for categories
    function addCatTranslation($data)
    {   
        $id =NULL;
        //get the existing translation id
        $sql = $this->select()
                    ->setIntegrityCheck(false)
                    ->from( 'translations_categories AS tc' ,array('tc.id'))
                    ->where('tc.category_id = ?', $data['category_id'])
                    ->where('tc.language_id = ?', $data['language_id']);
       
        $translationExist = $this->fetchRow($sql);
        //Zend_Debug::dump($sql->assemble($sql)); die();
        $date = new DateTime();
        $data = array(
            'category_id'  => $data['category_id'],
            'language_id'  => $data['language_id'],
            'status'  => $data['status'],
            'translation_title'  => $data['translation_title'],
            'date_added'=> date_format($date, 'Y-m-d H:i:s')
        );
        
        if($translationExist)
        {
            $this->update($data,'id ='.$translationExist['id']);
            return 'Translation updated';
        }
        else
        {
            $this->insert($data);
            return 'Translation inserted';
        }
    }
    
    //return translation and status of a category translation
    function getTranslationByCatIdLangId($categoryId,$langId)
    {
        $sql = $this->select()
                    ->setIntegrityCheck(false)
                    ->from('translations_categories as tc',array('translation_title','status'))
                    ->where('tc.category_id = ?',$categoryId)
                    ->where('tc.language_id = ?',$langId);
        return $this->fetchRow($sql);
    }
    
    //change status of the translation
    function statusCatTranslation($catId,$langId,$status){
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('category_id = ?', $catId);
        $where[] = $this->getAdapter()->quoteInto('language_id = ?', $langId);
        $data = array(
            'status'  => $status,
        );
        if($this->update($data,$where)):
            return 'Status updated';
        else:
            return 'Error on updating';
        endif;

    }
    
    //delete translation by cat id and lang id - no primary key
    function deleteCatTranslation($catId,$langId){
        
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('category_id = ?', $catId);
        $where[] = $this->getAdapter()->quoteInto('language_id = ?', $langId);
        
        //print_r($where);die();
        if($this->delete($where)){
            return 'Record deleted';
        }
        else{
            return 'Error on deleting';
        }
    }
    
    // Get category list accorigng to the selected language
    public function getCategoriesByLanguage($selectedLanguage,$page)
    {
         $sql = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('tc' => 'translations_categories'),
                        array('language_id', 'status', 'translation_title'))
                ->joinRight(array('c' => 'categories'),
                        'c.id = tc.category_id AND tc.language_id = '.$selectedLanguage,
                        array('id','name'))
                ->where('c.status = ?',1)
                //->where('tc.language_id = ?',$selectedLanguage)
                ->order('c.id');
     
                //Zend_Debug::dump($sql->assemble($sql)); die();
                $result = $this->fetchAll($sql); 
                
                //pagination      
                $paginator = Zend_Paginator::factory($result);
                $paginator->setItemCountPerPage(20);
                $paginator->setCurrentPageNumber($page); 
                return $paginator;
    }
    
    // ** this is a dump one. will be replaced ** Get category list accorigng to the selected language
//    public function getCategoriesNotByLanguage($selectedLanguage,$page)
//    {
//        
//        $sqlSub = $this->select()
//                ->setIntegrityCheck(false)
//                ->from(array('tc' => 'translations_categories'),
//                        array())
//                ->joinRight(array('c' => 'categories'),
//                        'c.id = tc.category_id',
//                        array('c.id'))
//                ->where('c.status = ?',1)
//                ->where('tc.language_id = ?',$selectedLanguage);
//        
//         $sql = $this->select()
//                ->setIntegrityCheck(false)
//                ->from(array('c' => 'categories'),
//                        array('*'))
//                ->where('c.id NOT IN ?',$sqlSub)
//                ->order('c.id');
//            
//                $result = $this->fetchAll($sql);   
//                //pagination      
//                $paginator = Zend_Paginator::factory($result);
//                $paginator->setItemCountPerPage(10);
//                $paginator->setCurrentPageNumber($page);        
//    
//       //Zend_Debug::dump($sql->assemble($sql)); //die();  
//        return $paginator;
//    }
}