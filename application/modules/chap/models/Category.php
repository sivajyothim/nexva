<?php
class Chap_Model_Category extends Model_Category {

    
    public function disableCategoryForChap($categoryId, $chapId) {
        $chapCatTable   = new Zend_Db_Table('chap_hidden_categories');
        $data   = array(
            'user_id'       => $chapId,
            'category_id'   => $categoryId
        );
        
        try {
            return $chapCatTable->insert($data);
        } catch (Zend_Db_Statement_Mysqli_Exception $zex) {
            //most probably a duplicate key, ignore it 
            return false;
        }
        return false;
    }
    
    public function enableCategoryForChap($categoryId, $chapId) {
        $chapCatTable   = new Zend_Db_Table('chap_hidden_categories');
        $data   = array(
            'user_id'       => $chapId,
            'category_id'   => $categoryId
        );
        $catIdWhere     = $chapCatTable->getAdapter()->quoteInto('category_id = ?', $categoryId);
        $userIdWhere    = $chapCatTable->getAdapter()->quoteInto('user_id = ?', $chapId);
        return $chapCatTable->delete("{$catIdWhere} AND {$userIdWhere}");
    }
    
    public function getDisabledCategoriesForChap($chapId) {
        $chapCatTable   = new Zend_Db_Table('chap_hidden_categories');
        $select         = $chapCatTable->select()->setIntegrityCheck(false);
        $select ->from('chap_hidden_categories', array())
                ->joinLeft('categories', 'categories.id = chap_hidden_categories.category_id', array('*'))
                ->where('user_id = ?', $chapId)
                ->order('parent_id ASC');
        return $chapCatTable->fetchAll($select);
    }
}