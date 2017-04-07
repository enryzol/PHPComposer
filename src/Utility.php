<?php 
namespace HjCommon;


class Utility{

	static function test(){
		echo 1;
	}
	
	//字串格式转化
	static function stringformat($str , $intype = "" , $outtype = ""){
		if(!in_array($intype , array('GBK' , 'UTF-8' , 'GB2312')) || !in_array($outtype , array('GBK' , 'UTF-8' , 'GB2312'))) {
			return $str;
		}
		if(empty($str)) {
			return $str;
		}
		if(is_array($str)){
			foreach ($str as $key=>$value){
				if(is_array($str[$key])){
					$str[$key] = stringformat($str[$key] , $intype , $outtype);
				}else{
					$str[$key] = iconv($intype , $outtype."//IGNORE" , $str[$key]);
				}
			}
		} else {
			$str = iconv($intype , $outtype , $str);
		}
		return $str;
	}
}



?>