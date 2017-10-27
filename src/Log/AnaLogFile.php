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
    public static $format = "\n[%s][URL:%s][IP:%s][LEVEL:%d]:\n=> %s\n";
    public static $format_after = "=> %s\n";
    
	public static function init ($file) {
	    global $Analog_File_Write;
		return function ($info, $buffered = false) use ($file) {
		    global $Analog_File_Write;
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
			        : vsprintf (AnaLogFile::$format, $info));
			}else{
			    if(is_array($info['message'])){
			        fwrite ($f, ($buffered)
			            ? $info
			            : vsprintf (AnaLogFile::$format_after, print_r($info['message'],true)));
			    }else{
			        fwrite ($f, ($buffered)
			            ? $info
			            : vsprintf (AnaLogFile::$format_after, $info['message']));
			    }
			    
			}
			
			flock ($f, LOCK_UN);
			fclose ($f);
		};
	}
}
