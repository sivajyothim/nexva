<?php
class Nexva_Product_Filter_Rule_Tag extends Nexva_Product_Filter_Rule_Abstract {
    
    protected $_requiredTables = array(
            'product_builds'        => array(
                'condition' => 'products.id = product_builds.product_id',
                'joinType'  => 'joinLeft',
                'select'    => array(),
            ),
            'product_build_tags'    => array(
                'condition' => 'product_build_tags.product_build_id = product_builds.id',
                'joinType'  => 'joinLeft',
                'select'    => array(),
            )
    );
    
    /**
     * @param Zend_Db_Table_Select $query
     */
    function modifyQueryObject($query = null) {
        
        $tags       = explode(',', $this->ruleRow->value);
        $symbol     = $this->ruleRow->type == 'EXCLUSION' ? '-' : '+';
        $symbol     = '';//don't need to implement proper boolean right now
        $tagQuery   = '';
        foreach ($tags as $tag) {
            $tagQuery   .= $symbol . $tag . ' ';
        } 
        if ($this->ruleRow->type == 'EXCLUSION') {
            $query->where('MATCH(product_build_tags.tags) AGAINST (? IN BOOLEAN MODE)', $tagQuery);
        } else {
            $query->where('MATCH(product_build_tags.tags) AGAINST (? IN BOOLEAN MODE)', $tagQuery);
        }
        return $query;
    }
}