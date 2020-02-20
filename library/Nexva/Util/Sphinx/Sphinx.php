<?php
/**
 * 
 * @copyright   neXva.com
 * @author      chathura <heshan at nexva dot com>
 * @package     Web
 * @version     $Id$
 */
include_once APPLICATION_PATH."/../library/Nexva/Util/Sphinx/sphinxapi.php";

class Nexva_Util_Sphinx_Sphinx  {

    public $limit  = 0; 
    
    /**
     * Get product ids by enterd search phrase
     * @param string  $searchPhrase
     * @return array $results containig product information
     */
	public function searchProducts($searchPhrase, $mode) {
		
		$mode = $mode;  
		$index = APPLICATION_ENV.'_'.'products';
		$results = $this->sphinx($searchPhrase, $mode, $index);
		return $results;
	
	}
	
	
 	/**
     * Get product ids by enterd search phrase
     * @param string  $searchPhrase
     * @return array $results containig product information
     */

	public function searchChapProducts($searchPhrase, $mode, $chapId) {
		
		$mode = $mode;  
		$index = APPLICATION_ENV.'_chap_'.$chapId.'_products';
		$results = $this->sphinx($searchPhrase, $mode, $index);
		return $results;
	}

    /**
     *
     */
    public function searchChapProducts1($searchPhrase, $mode, $chapId)
    {
        $mode = $mode;
        $index = APPLICATION_ENV.'_chap_'.$chapId.'_products';
        $results = $this->sphinx($searchPhrase, $mode, $index);
        return $results;
    }

    /**
	 * Get device ids by enterd search phrase
	 * @param string  $searchPhrase  search phrase
	 * @return array $results containig product information
	 */
	
	public function searchDevices($searchPhrase) {
		
	    //$mode = SPH_MATCH_PHRASE; 
		$mode = SPH_MATCH_EXTENDED; // this will match the  phrase  word by word
		$index = APPLICATION_ENV . '_' . 'devices';
		$results = $this->sphinx ( $searchPhrase, $mode, $index );
		return $results;
	
	}
	
	/**
     * Connect to the  sphinx Server and carryout the searching 
     * @param string  $searchPhrase search phrase
     * @param string  $mode search mode 
     * @param string   $index which index to be used
     * @return array $results containig product information
     */
	
	private function sphinx($searchPhrase, $mode, $index) {
		
		$q = $searchPhrase;
		
		/*
		 * SPH_MATCH_ALL, matches all query words (default mode);
		 * SPH_MATCH_ANY, matches any of the query words;
		 * SPH_MATCH_PHRASE, matches query as a phrase, requiring perfect match;
		 * SPH_MATCH_EXTENDED, matches query as an expression in Sphinx internal query language
		 * 
		 */
		
		$mode =  $mode; 
		$host = Zend_Registry::get ( 'config' )->nexva->application->search->sphinx->host;
		$port = ( int ) Zend_Registry::get ( 'config' )->nexva->application->search->sphinx->port;
		$index = $index;
		$groupby = "";
		$groupsort = "";
		$filter = "";
		$filtervals = array ();
		$distinct = "";
		$sortby = "";
		$sortexpr = '';
		$limit = $this->limit;
		$ranker = SPH_RANK_PROXIMITY_BM25;
		
		$cl = new SphinxClient ( );
		$cl->SetServer ( $host, $port );
		$cl->SetConnectTimeout ( 1 );
		$cl->SetWeights ( array (100, 1 ) );
		$cl->SetMatchMode ( $mode );
		
		if (count ( $filtervals )) {
			$cl->SetFilter ( $filter, $filtervals );
		}
		if ($groupby) {
			$cl->SetGroupBy ( $groupby, SPH_GROUPBY_ATTR, $groupsort );
		}
		if ($sortby) {
			$cl->SetSortMode ( SPH_SORT_RELEVANCE, $sortby );
		}
		if ($sortexpr) {
			$cl->SetSortMode ( SPH_SORT_EXPR, $sortexpr );
		}
		if ($distinct) {
			$cl->SetGroupDistinct ( $distinct );
		}
		if ($limit) {
			$cl->SetLimits ( 0, $limit, ($limit > 1000) ? $limit : 1000 );
		}
		$cl->SetRankingMode ( $ranker );
		$cl->SetArrayResult ( true );
		
		$res = $cl->Query ( $q, $index );

		return $res;
	
	}

	function indexProducts() {
	    $this->reindex(APPLICATION_ENV.'_'.'products');
	}
	
	/**
	 * 
	 * @todo CHECK PERMS! WON'T WORK TILL YOU FIX PERMS FOR THAT CALL
	 * re-indexes all databases. Used when products are deleted 
	 */
	function reindex($index = null) {
        $indexerPath    = Zend_Registry::get ('config')->nexva->application->search->sphinx->reindexCommand;
        if ($index) {
            exec($indexerPath . ' ' . $index);
        } else {
            exec($indexerPath . ' --all');
        }
        
	}
}

