<?php

/**
 * This factory is used to create instace of Direct Mobile Billing class of varoius telcos
 * @param  $type type of the class
 * @return instnace of the class
 * 
 * Maheel
 */
class Nexva_MobileBilling_Factory
{
    public static function createFactory($type)
    {
        $class = 'Nexva_MobileBilling_Type_'.($type);
        return new $class;
    }
}
