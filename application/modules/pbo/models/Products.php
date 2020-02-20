<?php
/**
 *

 */
class Pbo_Model_Products extends Zend_Db_Table_Abstract
{
    protected $_name = 'products';
    protected $_id = 'id';

    public function getNonChapProducts($chapId, $pricefilter = null, $category = null, $searchKey = null, $platform = null, $language = null, $grade = null)
    {
        $productSql   = $this->select();
        $productSql->from(array('p' => $this->_name), array('p.name','p.id','p.thumbnail','p.price','p.user_id','p.created_date','p.google_download_count'))
            ->setIntegrityCheck(false)
            ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
            ->join(array('c' => 'categories'), 'pc.category_id = c.id', array('c.name as cat_name'))
            ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array('pb.platform_id AS platform', 'pb.build_type','pb.id AS build_id'))
            ->join(array('pl' => 'platforms'),'pb.platform_id = pl.id',array('pl.description AS platform_name'))
            ->joinLeft(array('plm'=>'product_language_meta'),'p.id = plm.product_id',array('plm.id AS plm_id'))
            ->where('c.parent_id != ?', 0)
            ->where('p.id NOT IN (SELECT cp.product_id FROM chap_products cp WHERE cp.chap_id = '.$chapId.')')
            ->where('p.status = ?','APPROVED')
            ->where('p.deleted != ?',1)
            ->where('p.user_id != ?',5981)
            ->group('p.id')
            ->order('p.id DESC');

        if(!is_null($pricefilter) && !empty($pricefilter) && $pricefilter != 'all')
        {
            if($pricefilter == 'premium')
            {
                $productSql->where('p.price > ?',0);
            }
            else
            {
                $productSql->where('p.price = ?',0);
            }

        }

        if(!is_null($category) && !empty($category) && $category != 'all')
        {
            $productSql->where('pc.category_id = ?',$category);
        }

        if(!is_null($searchKey) && !empty($searchKey))
        {
            $productSql->where('p.name LIKE ?', '%'.$searchKey.'%');
        }

        if(!is_null($platform) && !empty($platform))
        {
            $productSql->where('pb.platform_id = ?',$platform);
        }

        if(!is_null($language) && !empty($language))
        {
            $productSql->where('(pb.language_id = ?) OR (plm.language_id = ?)',$language);
        }

        if(!empty($grade) && !is_null($grade)){
            $productSql ->join(array('qgc'=>'qelasy_grade_categories'),'qgc.category_id = c.id',array())
                ->where('qgc.grade_id = ?',$grade)
                ->where('qgc.status = ?',1)
            ;
            if(!is_null($category) && !empty($category) && $category != 'all'){
                $productSql ->where('qgc.category_id = ?',$category)
                ;
            }
        }
        //echo $productSql->assemble();die();
        return $productSql;

    }


    /**
     * Returns upload count
     * @param - $chapId
     * @param - $appType
     * @param - $fromDate
     * @param - $toDate
     */
    public function getUploadCount($chap_id, $appType=null ,$fromDate=null, $toDate=null)
    {
        $uploadSql = $this->select();
        $uploadSql->from(array('p' => $this->_name),array('count(p.id) as upload_count'))
            ->setIntegrityCheck(false)
            ->join(array('u' => 'users'), 'p.user_id = u.id', array(''))
            ->where('p.status = ?', 'APPROVED')
            ->where('p.deleted = ?', 0)
            ->where('u.chap_id = ?', $chap_id)
            ->where('u.type = ?', 'CP')
            ->where('u.status = ?', 1);

        if(!is_null($appType) && !empty($appType))
        {
            if($appType == 'free')
            {
                $uploadSql->where('p.price = ?', 0);
            }
            else
            {
                $uploadSql->where('p.price > ?', 0);
            }
        }

        if(!is_null($fromDate) && !is_null($toDate) &&!empty($fromDate) && !empty($toDate))
        {
            $uploadSql->where('DATE(p.created_date) >= ?', $fromDate)
                ->where('DATE(p.created_date) <= ?', $toDate);
        }

        $uploadCount =  $this->fetchRow($uploadSql);

        return $uploadCount->upload_count;
    }

    /**
     * Returns upload count
     * @param - $chapId
     * @param - $appType
     * @param - $fromDate
     * @param - $toDate
     */
    public function getUploadCountsByUser($userId, $userType)
    {
        $uploadSql = $this->select();
        $uploadSql->from(array('p' => $this->_name),array('count(p.id) as upload_count'))
            ->setIntegrityCheck(false)
            ->join(array('u' => 'users'), 'p.user_id = u.id', array(''))
            ->where('p.status = ?', 'APPROVED')
            ->where('p.deleted = ?', 0)
            ->where('u.id = ?', $userId)
            ->where('u.type = ?', $userType)
            ->where('u.status = ?', 1);

        $uploadCount =  $this->fetchRow($uploadSql);

        return $uploadCount->upload_count;
    }

    /**
     * Returns apps uploaded by the user
     * @param - $userId
     * @param - $userType
     */
    public function getUploadedAppsByUser($userId, $userType)
    {
        $uploadSql = $this->select();
        $uploadSql->from(array('p' => $this->_name),array('p.id', 'p.name'))
            ->setIntegrityCheck(false)
            ->join(array('u' => 'users'), 'p.user_id = u.id', array('DATE(p.created_date) as date'))
            ->where('p.status = ?', 'APPROVED')
            ->where('p.deleted = ?', 0)
            ->where('u.id = ?', $userId)
            ->where('u.type = ?', $userType)
            ->where('u.status = ?', 1);

        return $this->fetchAll($uploadSql)->toArray();
    }

    public function googleDownloads($id,$value){
        $data = array(
            'google_download_count'=>$value
        );
        $where = array(
            'id = ?' => $id
        );
        $this->update($data, $where);

        $sql = $this->select()
            ->from('products')
            ->where('products.id =  '.$id)
            ->query()
            ->fetchAll();
        return $sql;
    }

    public function getProductDetailsByProductID($productIDs)
    {
        $arr =  explode(',', $productIDs);
        $uniqueIDs = array_unique($arr);
        //return array_unique($uniqueIDs);
        $sql = $this    ->select();
        $sql            ->from(array('p'=>$this->_name))
                        ->setIntegrityCheck(false)
                        ->where('p.id IN (?)',$uniqueIDs);
        //return $sql->assemble();
        return $this->fetchAll($sql);
    }

    /*
     * This function will call when ajax request (auto complete) comes from pbo filter app section
     */
    public function getAllNonChapProductNames($chapId, $searchKey, $priceFilter = null, $category = null, $platform = null, $language = null)
    {
        $productSql   = $this->select();
        $productSql->from(array('p' => $this->_name), array('p.name'))
            ->setIntegrityCheck(false)
            ->join(array('pc' => 'product_categories'), 'pc.product_id = p.id', array())
            ->join(array('c' => 'categories'), 'pc.category_id = c.id', array())
            ->join(array('pb' => 'product_builds'), 'pb.product_id = p.id', array())
            ->join(array('pl' => 'platforms'),'pb.platform_id = pl.id',array())
            ->where('p.name LIKE ?', '%'.$searchKey.'%')
            ->where('c.parent_id != ?', 0)
            ->where('p.id NOT IN (SELECT cp.product_id FROM chap_products cp WHERE cp.chap_id = '.$chapId.')')
            ->where('p.status = ?','APPROVED')
            ->where('p.deleted != ?',1)
            ->where('p.user_id != ?',5981)
            ->group('p.id')
            ->limit(10, 0);

        if(!is_null($priceFilter) && !empty($priceFilter) && $priceFilter != 'all')
        {
            if($priceFilter == 'premium')
            {
                $productSql->where('p.price > ?',0);
            }
            else
            {
                $productSql->where('p.price = ?',0);
            }
        }

        if(!is_null($category) && !empty($category) && $category != 'all')
        {
            $productSql->where('pc.category_id = ?',$category);
        }

        if(!is_null($platform) && !empty($platform))
        {
            $productSql->where('pb.platform_id = ?',$platform);
        }

        if(!is_null($language) && !empty($language))
        {
            $productSql->where('pb.language_id = ?',$language);
        }
        
        //echo $productSql->assemble();
        $results =$this->fetchAll($productSql);
        
        if(count($results) > 0){
            return $results->toArray();
        }
        else{
            return array();
        }

    }
}