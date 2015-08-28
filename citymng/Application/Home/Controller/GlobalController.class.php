<?php
namespace Home\Controller;
use Think\Controller;
class GlobalController extends BaseController {
	protected $user;
	protected $city;
	protected $userName;
	protected $area_arr;
	protected $target_arr;
	
	function _initialize(){//var_dump(CONTROLLER_NAME);var_dump(ACTION_NAME);exit;
		parent::_initialize();
		
		A("Public")->detectLogin();
		
		$this->user = session('user');
		$this->city = session('city');
		$this->area_arr = explode(',', $this->user['area']);
		$this->target_arr = explode(',', $this->user['target']);
		
		$this->assign('current_user', $this->user);
		$this->assign('current_city', $this->city);
	}
	
	/*
	* 公共上传
	* @是否生成缩略图
	*/
	public function _upload($thumb = false, $width = 360, $height = 360, $pic='pic'){
		$root = 'upload/';
		$path = 'platform/' . date("Ym") . '/';
		
		if (!is_dir($root . $path))	mkdir($path);  
		
		$config = array(    
			'maxSize'    =>    1024 * 1024,
			'savePath'   =>    $path,
			'rootPath'	 =>	   $root,
			'saveName'   =>    'uniqid',
			'exts'       =>    explode('|',strtolower(C('ALLOW_IMG_TYPE'))),    
			'autoSub'    =>    false,    
		);
		$upload = new \Think\Upload($config);
		
		$info = $upload->uploadOne($_FILES[$pic]);
        if(!$info){
            //捕获上传异常
            return $upload->getError();
        }else{
			$return = array();
			
			//上传成功
			$file_path = $root . $info['savepath'] . $info['savename'];
			$return['file_path'] = $file_path;
//$fh = fopen('./test.txt', "a");
//fwrite($fh, $file_path);
//fclose($fh);			
			if ($thumb) {
				$thumb_file_path =  $root . $info['savepath'] . 'thumb_' . $info['savename'];
				
				$image = new \Think\Image(); 
				$image->open($file_path);
				$image->thumb($width, $height)->save($thumb_file_path);
				
				$return['thumb_path'] = $thumb_file_path;
			}
			
			return $return;
    	}
	}
	
	public function _upload2($file, $thumb = false, $width = 360, $height = 360){
		$root = C('UPLOAD_DIR');
		$path = date("Ym") . '/';
		
		if (!is_dir($root . $path))	mkdir($path);  
		
		$config = array(    
			'maxSize'    =>    10 * 1024 * 1024,
			'savePath'   =>    $path,
			'rootPath'	 =>	   $root,
			'saveName'   =>    time().'_'.mt_rand(10000,99999),
			'exts'       =>    explode('|',strtolower(C('ALLOW_IMG_TYPE'))),    
			'autoSub'    =>    false,    
		);
		$upload = new \Think\Upload($config);
		
		$info = $upload->uploadOne($file);
        if(!$info){
            //捕获上传异常
            return $upload->getError();
        }else{
			$return = array();
			
			//上传成功
			$file_path = $root . $info['savepath'] . $info['savename'];
			$return['file_path'] = $file_path;
//$fh = fopen('./test.txt', "a");
//fwrite($fh, $file_path);
//fclose($fh);			
			if ($thumb) {
				$thumb_file_path =  $root . $info['savepath'] . 'thumb_' . $info['savename'];
				
				$image = new \Think\Image(); 
				$image->open($file_path);
				$image->thumb($width, $height)->save($thumb_file_path);
				
				$return['thumb_path'] = $thumb_file_path;
			}
			
			return $return;
    	}
	}
	
	/*
		检查用户组权限
		@$module 可指定参数
	*/
	protected function checkSecurity($module,$action=''){
		if($action=='')$action='index';

		$groupId = $this->userType;
					//dump($module);exit;
		if($this->groupId!=1){
			if (!$groupId) $this->error('用户组非法');//用户组非法
			//获取用户组ID权限
			$security=D('Usertype')->where('rank='.$groupId)->find();
			if (!$security) {
				$this->error('未获取用户组ID权限，请联系系统管理员', U('Admin/Public/login'));
				exit;
			}
			
			$security['purviews']='|'.$security['purviews'];
			if($security['purviews']!='|ALL'){
				str_replace("|".strtolower($module)."/", "", $security['purviews'], $count);
	
				if($count<1){
					$this->error('未知模块，无法检查权限');
					exit;
				}
				if($action){
					str_replace("/".strtolower($action)."|", "", $security['purviews'], $count);
					if($count<1){
						$this->error('没有此操作的权限');
						exit;
					}		
				}
			}
		}
		/*
		$data['adminid']=$this->userId;
		$data['model']=$module;
		$data['action']=$action;
		if($_POST){
			str_replace("add","",strtolower($action),$addcount);
			str_replace("edit","",strtolower($action),$editcount);
		}else{
			str_replace("del","",strtolower($action),$delcount);
		}
		
		if($addcount>0||$editcount>0){
			foreach($_POST as $key => $val){
				if(strtolower($key)!='submit'&&strtolower($key)!='__hash__')$data['query'].=$key."=>".$val."|";
			}
		}elseif($delcount>0){
			foreach($_GET as $key => $val){
				$data['query'].=$key."=>".$val."|";
			}
		}
		$data['cip']=GetIP();
		$data['dtime']=C('NOWTIME');
		if($data['query']!='')D("log")->data($data)->add();
		*/
	}
}