<?php

namespace HjCommon\Database;

use HjCommon;

class Mssql {    
    var $link;    
    var $querynum = 0;
	var $db_address  = '';
	var $db_username = '';
	var $db_password = '';
	var $db_dbid = '';
	var $result = '';
	var $db_count = 0;
	var $chartrans = false;
	
    /*连接MSSql数据库，参数：dbsn->数据库服务器地址，dbun->登陆用户名，dbpw->登陆密码，dbname->数据库名字*/   
    function Connect($db_host = '', $db_username = '', $db_password = '', $db_dbid = ''){
    	global $_CONFIG;
    	
		$this->db_address 	= ($db_host=='')?$_CONFIG['MSSQL_DB_HOST']:$db_host;
		$this->db_username 	= ($db_username=='')?$_CONFIG['MSSQL_DB_USERNAME']:$db_username;
		$this->db_password 	= ($db_password=='')?$_CONFIG['MSSQL_DB_PASSWORD']:$db_password;
		$this->db_dbid 		= ($db_dbid=='')?$_CONFIG['MSSQL_DB_DBID']:$db_dbid;
		
        if(@!$this->link = odbc_connect($this->db_address, $this->db_username, $this->db_password )) {
		   $this->halt('Can not connect to MSSQL server');
        }
    }
   	
    /*执行sql语句，返回对应的结果标识*/   
    function Query($sql) {
    	
    	if(!$this->link){
    		$this->Connect();
    	}
    	$sql = str_replace("\\'","''",$sql);
    	$sql = str_replace('\&quot;','"',$sql);
    	$sql = iconv("UTF-8","GB2312//IGNORE",$sql);
    	
    	if(!empty($this->result))
    	odbc_free_result($this->result);
    	$query = odbc_do($this->link , $sql );
        if($query){
            $this->querynum++;
			$this->result = $query;
			return $this->result;
        } else {
            $this->querynum++;
            $this->halt('MSSQL Query Error', $sql);
            return false;
       }    
    }    
   
    /*执行sql语句，返回对应的结果标识*/   
    function db_update($sql) {
    	return $this->Query($sql);
    }    
   
    
    /*执行Insert Into语句，并返回最后的insert操作所产生的自动增长的id*/
    function Insert($table, $iarr) {
        $value = $this->InsertSql($iarr);
        $query = $this->Query('INSERT INTO ' . $table . ' ' . $value . '; SELECT SCOPE_IDENTITY() AS [insertid];');    
        $record = $this->GetRow($query);
        $this->Clear($query);
        return $record['insertid'];
    }
   
    /*执行Update语句，并返回最后的update操作所影响的行数*/   
    function Update($table, $uarr, $condition = '') {
    	
        $value = $this->UpdateSql($uarr);
        if ($condition) {    
            $condition = ' WHERE ' . $condition;    
        }    
        $query = $this->Query('UPDATE ' . $table . ' SET ' . $value . $condition . '; SELECT @@ROWCOUNT AS [rowcount];');       
        return 1;    
    }    
   
    /*执行Delete语句，并返回最后的Delete操作所影响的行数*/   
    function Delete($table, $condition = '') {    
        if ($condition) {    
            $condition = ' WHERE ' . $condition;    
        }
        $query = $this->Query('DELETE ' . $table . $condition . '; SELECT @@ROWCOUNT AS [rowcount];');    
        $record = $this->GetRow($query);    
        $this->Clear($query);    
        return $record['rowcount'];    
    }    
   
    /*将字符转为可以安全保存的mssql值，比如a'a转为a''a*/   
    function EnCode($str) {    
        return str_replace("'" , "''", str_replace('', '', $str));    
    }    
   
    /*将可以安全保存的mssql值转为正常的值，比如a''a转为a'a */   
    function DeCode($str) {    
        return str_replace("''", "'", $str);    
    }    
   
    /*将对应的列和值生成对应的insert语句，如：array('id' => 1, 'name' => 'name')返回([id], [name]) VALUES (1, 'name')*/   
    function InsertSql($iarr) {    
        if (is_array($iarr)) {    
            $fstr = '';    
            $vstr = '';    
            foreach ($iarr as $key => $val) {    
                $fstr .= '[' . $key . '], ';    
                $vstr .= "'" . $val . "', ";    
            }    
            if ($fstr) {    
                $fstr = '(' . substr($fstr, 0, -2) . ')';    
                $vstr = '(' . substr($vstr, 0, -2) . ')';    
                return $fstr . ' VALUES ' . $vstr;    
            } else {    
                return '';    
            }    
        } else {    
            return '';    
        }    
    }    
   
    /*将对应的列和值生成对应的insert语句，如：array('id' => 1, 'name' => 'name')返回[id] = 1, [name] = 'name'*/   
    function UpdateSql($uarr) {
        if (is_array($uarr)) {
            $ustr = '';
            foreach ($uarr as $key => $val) {
                $ustr .= '[' . $key . "] = '" . $val . "', ";
            }
            if ($ustr) {
                return substr($ustr, 0, -2);
            } else {
                return '';
            }
        } else {    
            return '';    
        }
    }    
   
    /*返回对应的查询标识的结果的一行*/   
    function db_getRow($sql = '') {
    	$sql = str_replace("\\'","''",$sql);
   		if($sql != '' && $query = $this->Query($sql)){
            $this->querynum++;
            if($this->chartrans){
            	return HjCommon\Utility::stringformat(odbc_fetch_array($query),"GB2312","UTF-8");
            }else{
            	return odbc_fetch_array($query);
            }
            
        }else{
        	if($this->chartrans){
        		return HjCommon\Utility::stringformat(odbc_fetch_array($this->result),"GB2312","UTF-8");
        	}else{
        		return odbc_fetch_array($this->result);
        	}
			
		}
    }
    
    function select($sql , $offset=0 , $limit=0){
    	return $this->db_getResultArr($sql , $offset=0 , $limit=0);
    }
    
    //获取数据列
	function db_getResultArr($sql , $offset=0 , $limit=0){
		$sql = str_replace("\\'","''",$sql);
		if($this->Query($sql)){
			$arr = array();

			if($offset!=0 || $limit!=0){
				
				$temp = '';
				$i = 0;

				for($j=0;$j<$offset;$j++){
					odbc_fetch_row($this->result);
				}
				
				while(@$temp = odbc_fetch_array($this->result)){

					if($i>=$limit){
						break;
					}
					$i++;
					$offset++;

					if(!$temp) break;
					$arr[] = $temp;
				}
				$arr = HjCommon\Utility::stringformat($arr,"GB2312","UTF-8");
			}else{
				while(@$temp = $this->db_getRow()){
					
					if(!$temp) break;
					$arr[] = $temp;
				}
			}
			return $arr;
		}else{
			return false;
		}
	}
	
    /*清空查询结果所占用的内存资源*/
    function Clear($query) {
        return mssql_free_result($query);    
    }    
   
    /*关闭数据库*/   
    function Close() {    
        return odbc_close($this->link);    
    }    
   
    function halt($message = '', $sql = '') {  
    	if(!$this->link){
    		$message .= '<br />MSSql Error: ';
    	}else{
    		$message .= '<br />MSSql Error:' . odbc_errormsg($this->link) . odbc_error($this->link);
    	}
            
        
        if ($sql) {    
            $sql = '<br />sql:' . $sql;    
        } 
        $message = iconv("GB2312", "UTF-8", $message . $sql);
    }    
}    

