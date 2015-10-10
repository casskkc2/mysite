<?php
namespace Server\Controller;
use Think\Controller;
class PublicController extends Controller {
	private $err = array(
		'AUTH_FAIL' => '用户名或密码错误'
	);
	
	public function login() {
		if ($_POST) {
			$json = array('code'=>0, 'error'=>'', 'data'=>array());
			
			$username = I('post.username');
			$password = I('post.password');
			$AccountEvent = A('Home/Account', 'Event');
			$user = $AccountEvent->auth($username, $password);
			if (!$user) {
				$json['error'] = $this->err['AUTH_FAIL'];
				$json['code'] = 1;
				
				exit(json_encode($json));
			}
			$user_type = M('UserType')->where(array('user_type_id'=>$user['user_type_id']))->find();
			$city = M('City')->alias('c')
				->join('province p ON c.father=p.province_id')
				->field('c.city_id, c.city, p.province_id, p.province')
				->where(array('c.city_id'=>$user['city_id']))
				->find();
			session('user', $user);
			session('city', $city);
			$json['data']['user'] = $user;
			$json['data']['city'] = $city;
			
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
			
			exit(json_encode($json));
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

}