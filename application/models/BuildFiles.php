<?php
class Model_BuildFiles extends Zend_Db_Table_Abstract {


    protected $_id = 'id';
    protected $_name = 'build_files';

    function __construct() {
        parent::__construct();
    }
	
    /**
     * Get sum of the file size for given build 
     * @param int $buildId 
     * @return int sum of files sizes rounded in to nearest KB
     * Chathura
     */ 
    
	function getBuildFileSize($buildId) {
		
		$fileInfo = $this->fetchAll ( $this->select ()->from ( 'build_files', 'SUM(filesize) as filesize' )->where ( 'build_id =' . $buildId ) );
		
		return round($fileInfo[0]->filesize/1024);
	}
	
	public function updateFileSize($fileSize, $id) {
		$data = array ('filesize' => $fileSize );
		$this->update ( $data, array ('id = ?' => $id ) );
	}

    public function getFileType($id){
        $sql = $this->select();
        $sql    ->from(array('bf' => $this->_name),array('bf.filename'))
                ->where('bf.build_id = ?',$id)
                ;
        return $this->fetchAll($sql);
    }
    
    public function getFileTypeapi($id){
    	$sql = $this->select();
    	$sql    ->from(array('bf' => $this->_name),array('bf.filename'))
    	->where('bf.build_id = ?',$id)
    	;
    	
    	if($this->fetchRow($sql)) {
            return $this->fetchRow($sql);
    	} else {
    	    return false;
    	}
    }
    
}
