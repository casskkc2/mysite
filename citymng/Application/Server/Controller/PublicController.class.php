<?php
namespace Server\Controller;
use Think\Controller;
class PublicController extends Controller {
	private $err = array(
		'AUTH_FAIL' => '用户名或密码错误'
	);
	
	public function login() {
		if ($_POST) {
			$json = array('code'=>0, 'error'=>'', 'data'=>null);
			
			$username = I('post.username');
			$password = I('post.password');
			$AccountEvent = A('Home/Account', 'Event');
			$user = $AccountEvent->auth($username, $password);
			if (!$user) {
				$json['error'] = $this->err['AUTH_FAIL'];
				$json['code'] = 1;
				
				//exit(json_encode($json));
				$this->ajaxReturn($json, 'JSON');
			}
			$user_type = M('UserType')->where(array('user_type_id'=>$user['user_type_id']))->find();
			$city = M('City')->alias('c')
				->join('province p ON c.father=p.province_id')
				->field('c.city_id, c.city, p.province_id, p.province')
				->where(array('c.city_id'=>$user['city_id']))
				->find();
			session('user', $user);
			session('city', $city);
			$json['data'] = array();
			$json['data']['user'] = $user;
			$json['data']['city'] = $city;
			
			$area_arr = explode(',', $user['area']);
			$AreaEvent = A('Home/Area', 'Event');
		    $areaList = $AreaEvent->getAreaListByIds($user['city_id'], $area_arr);
		    $all_area_arr = $area_arr;
		    foreach($areaList as $row) {
			    $all_area_arr = array_merge($all_area_arr, treePathToArray($row['path']));
    		}
    		$all_area_arr = array_unique($all_area_arr);
    		
			$cond = array('status'=>0, 'city_id'=>$user['city_id']);
        	if (!empty($all_area_arr) && !empty($all_area_arr[0])) {
        		$cond['id'] = array('in', $all_area_arr);
        	}
			
		    $target_arr = explode(',', $user['target']);
    		$TargetEvent = A('Home/Target', 'Event');
    		$targetList = $TargetEvent->getTargetsByIds($target_arr);
    		$all_target_arr = $target_arr;
    		foreach($targetList as $row) {
    			$all_target_arr = array_merge($all_target_arr, treePathToArray($row['path']));
    		}
    		$all_target_arr = array_unique($all_target_arr);
    		
			$cond_target = array('status'=>0, 'city_id'=>$user['city_id']);
        	if (!empty($all_target_arr) && !empty($all_target_arr[0])) {
        		$cond_target['id'] = array('in', $all_target_arr);
        	}
        	
			$json['data']['area'] = M('Area')->field('id, name, path')->where($cond)->order('sort')->select();
			$json['data']['target'] = M('Target')->field('id, name, path, code, sort')->where($cond_target)->order('sort')->select();
			
			$data = array(
				'last_login_ip' => $_SERVER['REMOTE_ADDR'],
				'last_login_time' => date('Y-m-d H:i:s')
			);
			M('User')->where(array('id'=>$user['id']))->data($data)->save();
			
			// save login history
			$data = array(
				'user_id'	=> $user['id'],
				'city_id'	=> $user['city_id'],
				'create_time'	=> date('Y-m-d H:i:s'),
				'ip'		=> $_SERVER['REMOTE_ADDR']
			);
			M('LoginHistory')->data($data)->add();

			$this->ajaxReturn($json, 'JSON');
		}
	}
	/*
	public function detectLogin() {
		if (!session('?user') || !session('?city')) {
			session('user', null);
			session('city', null);
			$this->redirect('Home/Public/index');
		}
	}*/
	
	public function getTime() {
	    $json = array('code'=>0, 'error'=>'', 'data'=>time());
	    $this->ajaxReturn($json, 'JSON');
	}
	
	public function getApkInfo() {
	    $json = array('versionCode'=>7, 'versionName'=>'1.1.1', 'versionUrl'=>'http://59.172.252.38:8081/download/app/citymng.apk');
	    $this->ajaxReturn($json, 'JSON');
	}
}