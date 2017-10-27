<?php

namespace HjCommon\Log;

/**
 * Append to the specified log file. Does the same thing as the default
 * handling.
 *
 * Usage:
 *
 *     $log_file = 'log.txt';
 *     Analog::handler (Analog\Handler\File::init ($log_file));
 *     
 *     Analog::log ('Log me');
 *
 * Note: Uses Analog::$format for the appending format.
 */
class AnaLogFile {
    public static $format = "\n[%s][IP:%s][URL:%s]:\n=> %s\n";
    public static $format_after = "=> %s\n";
    
	public static function init ($file) {
	    global $Analog_File_Write;
		return function ($info, $buffered = false) use ($file) {
		    global $Analog_File_Write;

		    $log = array (
		        'date' => $info['date'],
		        'machine' => $info['machine'],
		        'url' => $_SERVER['REQUEST_URI'],
// 		        'level' => $info['level'],
		        'message' => $info['message']
		    );
		    
			$f = fopen ($file, 'a+');
			if (! $f) {
				throw new \LogicException ('Could not open file for writing');
			}
	
			if (! flock ($f, LOCK_EX)) {
				throw new \RuntimeException ('Could not lock file');
			}
			
			if(!isset($Analog_File_Write[$file])){
			    $Analog_File_Write[$file] = true;
			    fwrite ($f, ($buffered)
			        ? $info
			        : vsprintf (AnaLogFile::$format, $log));
			}else{
			    if(is_array($info['message'])){
			        fwrite ($f, ($buffered)
			            ? $info
			            : vsprintf (AnaLogFile::$format_after, print_r($log['message'],true)));
			    }else{
			        fwrite ($f, ($buffered)
			            ? $info
			            : vsprintf (AnaLogFile::$format_after, $log['message']));
			    }
			    
			}
			
			flock ($f, LOCK_UN);
			fclose ($f);
		};
	}
}
