<?php
namespace HjCommon\Auth;

use think\Config;
use think\Controller;
use think\Debug;

/**
 * @author Hjay
 *  函数格式
 *  public $edit = [
 *      "name"=>"角色编辑管理", //菜单显示名称
 *      'hide'=>true,  //菜单是否显示在菜单列表上
 *      'active'=>'rolelist' //当前页菜单激活的Action
 *  ];
	function edit(){
		return;
	}
 *
 */
class HjCheck extends Controller{
    
    protected $MenuLeft ;
    protected $Menu ;
    protected $AuthMenu;
    protected $su = 0;
    protected $Db_Role = 'h_role';
    protected $Db_Role_Auth = 'h_role_auth';
    
	protected $beforeActionList = [
		'checkLogin','getMenu'
	];
	function checkLogin(){

		if(empty(session('AuthRoleID'))){
	        $this->error('非法访问','/');
	    }
	    $request = \think\Request::instance();
	    
	    //超级管理员
	    $or = db($this->Db_Role);
	    $rinfo = $or->where(['roleid'=>session('AuthRoleID')])->find();
	    $this->su = $rinfo['su'];
	    
	    $ob = db($this->Db_Role_Auth);
	    
	    //验证权限 - 除了超级管理员
	    if($this->su == 0){
		    $info = $ob->where([
		        'roleid'=>session('AuthRoleID'),
		        'menu'=>$request->controller()."_".$request->action()
		    ])->find();
		    
		    if(!$info){
		        $this->error('非法访问');
		    }
	    }
	    
	    $tmp = $ob->where([
	        'roleid'=>session('AuthRoleID')
	    ])->select();
	   	
	    //读取数据库权限配置归类
	    foreach($tmp as $key=>$value){
	        $tmp1 = explode('_', $value['menu']);
	        if(isset($tmp1[1])){
	            $this->AuthMenu[$tmp1[0]][$tmp1[1]] = true;
	        }
	    }
	    
	    return true;
	}
	function getMenu(){
		$Module = Config::get('AuthModule'); //获取配置菜单文件
		$MenuLeft = [];  //普通用户菜单组
		$Menu = [];
		$MenuSuperUser = []; //超级管理员 菜单组
		
		$request = \think\Request::instance();
		$CurrentUrl = $request->controller().'_'.$request->action();
		
		//遍历每个类配置
		foreach($Module['MuduleList'] as $key=>$value){
		    //$value = 每个类
		    
		    //$class_methods获取每个大类的方法
			$class_methods = get_class_methods($Module['Namespace'].'\\'.$value['Mudule']);
			if(empty($class_methods)){ continue;}
			//$class_vars获取每个大类的方法的描述
			$class_vars = get_class_vars($Module['Namespace'].'\\'.$value['Mudule']);
			
			//菜单列表
			//$Menu[System] = 大菜单配置
			$Menu[$value['Mudule']]['name'] = $value['Name'];
			$Menu[$value['Mudule']]['module'] = $value['Mudule'];
			
			//当模块设置为MenuShow = false 的时候，模块不显示在菜单上 大菜单
			if($value['MenuShow']!==false){
				$MenuLeft[$value['Mudule']]['name'] = $value['Name'];
				$MenuLeft[$value['Mudule']]['module'] = $value['Mudule'];
				
				$MenuSuperUser[$value['Mudule']]['name'] = $value['Name'];
				$MenuSuperUser[$value['Mudule']]['module'] = $value['Mudule'];
			}
			
			//$class_methods每个大类的方法
			foreach($class_methods as $key1=>$value1){
			    
			    //读取action配置
			    if(!array_key_exists($value1,$class_vars)){
			        continue;
			    }
			    
			    //$class_vars每个大类的方法的描述
			    $config = ($class_vars[$value1]);
			    if(!isset($config['hide'])){
			        $config['hide'] = false;
			    }
			    
				if(!empty($class_vars[$value1])){
				    
				    if(isset($config['name'])){
				        $Menu[$value['Mudule']]['value'][] = [
				            'name'	=> 	$config['name'],
				            'action'=> 	$value1
				        ];
				    }
					
				}
				//$value['MenuShow'] 大菜单设置的显示设置
				//!empty($config) 不为空的配置信息
				//$config['hide']
				if($value['MenuShow'] && !empty($config) && ($config['hide'])!==true){
				    //比对数据库
				    if(isset($this->AuthMenu[$value['Mudule']][$value1])){
				        $MenuLeft[$value['Mudule']]['value'][] = [
    						'name'	      => 	isset($config['name'])?$config['name']:"",
    						'active'	  => 	isset($config['active'])?$config['active']:$value1,
    						'module'      => 	$value['Mudule'],
    						'action'      => 	$value1,
    						'url'	      =>    URL($value['Mudule'].'/'.$value1)
    					];
				    }
    					
				    $MenuSuperUser[$value['Mudule']]['value'][] = [
			    		'name'	      => 	isset($config['name'])?$config['name']:"",
			    		'active'	  => 	isset($config['active'])?$config['active']:$value1,
			    		'module'      => 	$value['Mudule'],
			    		'action'      => 	$value1,
			    		'url'	      =>    URL($value['Mudule'].'/'.$value1)
				    ];
				}
				
				if(strtolower($request->controller()) == strtolower($value['Mudule']) 
				    && strtolower($request->action()) == strtolower($value1)){
				    //遍历所有菜单，得到当前菜单取出配置
				    $tmp = isset($config['active'])?$config['active']:$value1;
				    $CurrentUrl = $value['Mudule']."_".$tmp;
				}
			}
		}
		
		$this->Menu = $Menu;
		$this->MenuLeft = $MenuLeft;
		
		//超级管理员赋予所有目录
		if($this->su == 1){
			$this->MenuLeft = $MenuSuperUser;
		}
		
		//删除空菜单
		foreach($this->MenuLeft as $key2=>$value2){
			if(empty($value2['value'])){
				unset($this->MenuLeft[$key2]);
			}
		}
		
		$this->assign('CurrentUrl',$CurrentUrl);
		$this->assign('Action',$request->action());
		$this->assign('Controller',$request->controller());
		$this->assign('Menu',$this->Menu);
		$this->assign('MenuLeft',$this->MenuLeft);
	}
	
	function getAuthMenu($roleid){
	    $ob = \think\Db::name($this->Db_Role_Auth);
	    $list = $ob->where(['roleid'=>$roleid])->select();
	    return $list;
	}
	
	function saveAuthMenu($roleid,$menus,$rolename='',$remark=''){
	    $or = db($this->Db_Role);
	    $ob = db($this->Db_Role_Auth);
	    
	    if(empty($roleid)){
	        $or->insert(['rolename'=>$rolename,'remark'=>$remark]);
	        $roleid = $or->getLastInsID();
	    }
	    
	    $ob->where(['roleid'=>$roleid])->delete();
	    
	    if(is_array($menus)){
	        $add = [];
	        foreach($menus as $key=>$value){
	            if(empty($value)){
	                continue;
	            }
	            $add[] = ['roleid'=>$roleid,'menu'=>$value];
	        }
	        $ob->insertAll($add);
	    }
	}
	
	static function login($roleid){
	    session('AuthRoleID',$roleid);
	}
	
	static function logout(){
	    session('AuthRoleID',null);
	}
	
	static function getRoleID(){
		return session('AuthRoleID');
	}
	
	
	
	
	
	
	
	
}