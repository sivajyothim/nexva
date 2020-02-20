<?php
class Model_ProductBuildTag extends Nexva_Db_Model_MasterModel {
    
    protected  $_id     = 'id';
    protected  $_name   = 'product_build_tags';
    
    /**
     * 
     * Returns all the enabled tags defined in the system
     */
    public function getAvailableTags() {
        $availableTagTable  = new Zend_Db_Table('product_build_available_tags');
        return $availableTagTable->fetchAll('enabled = 1');
    }

    /**
     * 
     * Returns an array of ProductBuildTag objects given the build ID
     * @param $buildIds Array
     */
    public function getBuildTagsByBuildId($buildIds) {
        $select     = $this->select()->where('product_build_id IN (?)', $buildIds);
        return $this->fetchAll($select);
    }
    
    /**
     * This method pads the tags so that the minimum index limit is met in mysql
     */
    public function padTag($tags) {
        $tagArr     = explode(',', $tags);
        $padTags    = array();
        foreach ($tagArr as $tag) {
            $padTags[]    = 'NXTAG_' . trim($tag);
        }
        return implode(' , ', $padTags);
    }

    /**
     * Removes the padding that was put on the tag
     */
    public function unPadTag($tag) {
        return str_replace('NXTAG_', '', $tag);
    }
}