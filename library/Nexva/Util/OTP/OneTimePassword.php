<?php

/**
 * An implementation of a secure schema-free one-time password algorithm described by Amir Shevat
 *
 * @see http://spacebug.com/tableless_secure_one_time_password/
 * @author jahufar
 * @package Nexva
 *
 */

class Nexva_Util_OTP_OneTimePassword
{
    protected $time = null;
    protected $salt = null;

    protected $id = null;
    protected $password = null;

    /**
     *
     *
     * @param int $id
     * @param string $password
     */
    public function  __construct($id = null, $password = null)
    {
        $this->id = $id;
        $this->password = $password;
        $this->time = strtotime("+12 hours"); //default timeout is 12 hours
        $this->salt = Zend_Registry::get('config')->nexva->application->salt;
    }

    /**
     * Sets user ID
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Sets password for user identified by $id
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Additional data to be used as a hash salt for more security.
     * This should be a global constant in your application.
     *
     * @param string $salt
     * @return null
     */
    function setSalt($salt)
    {
        $this->salt = $salt;

    }

    /**
     * Sets the time for how long the reset hash is to be active. $time should be valid unix timestamp.
     * Defaults to 12 hours from current system time.
     *
     * @param long $time
     * @return null
     */
    function setTimeout($time)
    {
        $this->time = $time;
    }


    /**
     * Generates a secure one time password (OTP) hash
     *
     * @return string
     * @throws Zend_Exception
     */
    public function generateOTPHash()
    {
        if( is_null($this->password) || is_null($this->id)  )
            throw new Zend_Exception('Id or Password not set.');

        $hash = md5( md5($this->password). $this->id. $this->time. $this->salt );

        return $hash;
    }

    /**
     * Verifies of the OTP hash is valid
     * 
     * @param string $hash
     * @return boolean
     */
    public function verifyOTPHash($hash)
    {
        if( strtotime("now") > $this->time ) return false; //request expired.
        //Zend_Debug::dump($this);

        //die( md5( md5($this->password). $this->id. $this->time. $this->salt )." == ". $hash);
        
        return  md5( md5($this->password). $this->id. $this->time. $this->salt ) == $hash ;       
    }


}

?>