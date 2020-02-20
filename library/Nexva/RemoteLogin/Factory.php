<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 6/23/14
 * Time: 6:13 PM
 * To change this template use File | Settings | File Templates.
 * This factory is used to create instance of Login class for special telcos, which user registration is doing by their side
 */

class Nexva_RemoteLogin_Factory
{
    public static function createFactory($type)
    {
        $class = 'Nexva_RemoteLogin_'.($type);
        return new $class;
    }
}
