<?php

class Admin_Model_LanguageUser extends Model_Language
{
    protected $_name = 'language_users';
    protected $_id = 'id';

    /**
     * @param $id
     * @param $language
     * @param $status
     * @return string
     */
    function addLanguageUser($id,$language)
    {
        $sql = $this->select()
                    ->setIntegrityCheck(false)
                    ->from( 'language_users AS l' ,array('l.id'))
                    ->where('l.user_id = ?', $id)
                    ->where('l.language_id = ?',$language)
                    ;
        
        $exist = $this->fetchAll($sql)->toArray();
        $date = new DateTime();
        $data = array(
            'language_id'  => $language,
            'status'    => 1
            //'date_added'=> date_format($date, 'Y-m-d H:i:s')
        );
        if($exist)
        {
            $this->update($data,'user_id ='.$id.' AND language_id ='.$language);
            //return 'Language Updated';
        }
        else
        {
            $data['user_id'] = $id;
            $data['date_added'] = date_format($date, 'Y-m-d H:i:s');
            $this->insert($data);
            //return 'Language Inserted';
        }
    }

    public function removeLanguageUser($chap_id,$language_id){
        $sql = $this->select();
        $sql    ->from(array('lu' => $this->_name))
                ->where('lu.user_id = ?',$chap_id)
                ->where('lu.language_id = ?',$language_id)
                ;
        $result = $this->fetchAll($sql);
        if(count($result) > 0){
            $data = array
            (
                'status' => 0
            );
            $this->update($data,'user_id ='.$chap_id.' AND language_id = '.$language_id);
        }
    }


    /**
     * @param $chap_id
     * @return array
     */
    function getLanguageUser($chap_id)
    {
       
        $sql = $this->select()    
                    ->setIntegrityCheck(false)
                    ->from(array('lu' => $this->_name),array('lu.language_id', 'lu.status AS language_assigned','lu.default_language'))
                    ->joinRight(array('l'=>'languages'),'lu.language_id = l.id AND lu.user_id = '.$chap_id)
                    ->where('l.status = 1')
                    ;

        //echo $sql->assemble();die();

        $languageUser = $this->fetchAll($sql)->toArray();
        return $languageUser;
    }


}