<?php
class Mobile_Model_Category extends Model_Category {

    /**
     * Returns the enabled root categories for a chap
     */
    public function getRootCategoriesForChap($chapId) {
        $chapCategory   = new Chap_Model_Category();
        $disabledCats   = $chapCategory->getDisabledCategoriesForChap($chapId);
        $select         = $this->select()->where('parent_id = ?', 0)->where('status = ?', 1);
        if (count($disabledCats)) {
            //remove the disabled cats
            $disabledCatList    = array();
            foreach ($disabledCats as $cat) {
                $disabledCatList[$cat->id]  = $cat->id;
            }
            $select->where('id NOT IN (?)', $disabledCatList);
        }
        $select->order('name ASC');
        return $this->fetchAll($select);
    }
    
    /**
     * Get all the categories
     */
    public function getCategorylist($chapId = null) {
        $chapCategory   = new Chap_Model_Category();
        $disabledCats   = $chapCategory->getDisabledCategoriesForChap($chapId);
        
        $select = $this->select()->where('status = ?', 1)->order('id ASC')->order('parent_id ASC');
        
        if (count($disabledCats)) {
            //remove the disabled cats
            $disabledCatList    = array();
            foreach ($disabledCats as $cat) {
                $disabledCatList[$cat->id]  = $cat->id;
            }
            $select->where('id NOT IN (?)', $disabledCatList);
        }
        
        $resultSet = $this->fetchAll($select);
        
        $entries = array();
        foreach ($resultSet as $row) {
            $entries[$row->parent_id][$row->id] = $row->name;
        }
        
        return $entries;
    }
}