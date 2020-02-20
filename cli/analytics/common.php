<?php
    define('LOCK_FILE', 'LOCK');
    

    function e($txt) {
        echo $txt . "\n";
    }

    function lock($process = null) {  
        if (!$process) {
            die("I need a unique process name");
        }
        
        $file   = LOCK_FILE . $process;
        if (file_exists($file)) {
            $log    = file_get_contents($file);
            die('Could not get lock ' . $log);
        } else {
            file_put_contents($file, $process . ' STARTED : ' . date('d-m-y h:i:s'));    
        }
        
    }
    
    function unlock($process = null) {
        if (!$process) {
            die("I need a unique process name");
        }
        $file   = LOCK_FILE . $process;
        if (file_exists($file)) {
            unlink($file);
        }
    }