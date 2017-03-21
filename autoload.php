<?php
/**
 * 自动载入
 *
 */
spl_autoload_register(function ($classname) {
    $baseDir = __DIR__  . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
	
    if (strpos($classname, "HjCommon\\") === 0) {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, 
        		substr($classname, strlen('hjcommon\\')));
        $file = $baseDir . $path . '.php';

        if (is_file($file))
            require_once $file;
    }
});