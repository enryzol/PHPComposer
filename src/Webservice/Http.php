<?php
namespace HjCommon\Webservice;

class Http {
	static function Message($stausCode,$data,$message="") {
		$Message['stausCode'] 	= $stausCode;
		$Message['data'] 		= $data;
		$Message['message'] 	= $message;
		foreach($Message as $key=>$value){
			if(!isset($value)){
				unset($Message[$key]);
			}
		}
		return json_encode($Message);
	}
}