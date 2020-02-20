<?php
    include_once("../../application/BootstrapCli.php");

    //get the cache object and see what the site version is
    $cache  = new Nexva_Cache_Base();
    $cache->remove('SITE_VERSION');
// test comment

