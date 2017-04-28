<?php
namespace HjCommon\Auth;

use think\Config;
use think\Controller;

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
    
	protected $beforeActionList = [
		'checkLogin','getMenu'
	];
	function checkLogin(){
	    if(empty(session('AuthRoleID'))){
	        $this->error('非法访问','/');
	    }
	    $request = \think\Request::instance();
	    $ob = db('role_auth');
	    $info = $ob->where([
	        'roleid'=>session('AuthRoleID'),
	        'menu'=>$request->controller()."_".$request->action()
	    ])->find();
	    if(!$info){
	        $this->error('非法访问');
	    }
	    $tmp = $ob->where([
	        'roleid'=>session('AuthRoleID')
	    ])->select();
	    
// 	    print_r($tmp);
	    //读取数据库权限配置归类
	    foreach($tmp as $key=>$value){
	        $tmp1 = explode('_', $value['menu']);
	        if(isset($tmp1[1])){
	            $this->AuthMenu[$tmp1[0]][$tmp1[1]] = true;
	        }
	    }
// 	    print_r($this->AuthMenu);
	}
	function getMenu(){
		$Module = Config::get('AuthModule');
		$MenuLeft = [];
		$Menu = [];
		
		$request = \think\Request::instance();
		$CurrentUrl = $request->controller().'_'.$request->action();
		
		foreach($Module['MuduleList'] as $key=>$value){
			$class_methods = get_class_methods($Module['Namespace'].'\\'.$value['Mudule']);
			if(empty($class_methods)){ continue;}
			$class_vars = get_class_vars($Module['Namespace'].'\\'.$value['Mudule']);
			
			
			//菜单列表
			$Menu[$value['Mudule']]['name'] = $value['Name'];
			$Menu[$value['Mudule']]['module'] = $value['Mudule'];
			
			//当模块设置为MenuShow = false 的时候，模块不显示在菜单上
			if($value['MenuShow']!==false){
				$MenuLeft[$value['Mudule']]['name'] = $value['Name'];
				$MenuLeft[$value['Mudule']]['module'] = $value['Mudule'];
			}
			foreach($class_methods as $key1=>$value1){
			    
			    //读取action配置
			    if(!array_key_exists($value1,$class_vars)){
			        continue;
			    }
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
    					
				}
				
				if($request->controller() == $value['Mudule'] && $request->action() == $value1){
				    $tmp = isset($config['active'])?$config['active']:$value1;
				    $CurrentUrl = $value['Mudule']."_".$tmp;
				}
			}
		}
		
		$this->Menu = $Menu;
		$this->MenuLeft = $MenuLeft;
		
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
	    $ob = \think\Db::name('role_auth');
	    $list = $ob->where(['roleid'=>$roleid])->select();
	    return $list;
	}
	
	function saveAuthMenu($roleid,$menus,$rolename='',$remark=''){
	    $or = db('role');
	    $ob = db('role_auth');
	    
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
	
	
	
	
	
	
	
	
}