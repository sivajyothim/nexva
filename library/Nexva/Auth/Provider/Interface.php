<?php
interface Nexva_Auth_Provider_Interface {
    /**
     * Method that actually does the login process. Everything branches out from here
     */
    function doLogin();
    
    function beginLogin();
}