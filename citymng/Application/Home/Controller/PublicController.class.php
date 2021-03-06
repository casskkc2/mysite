<?php
namespace Home\Controller;
use Think\Controller;
class PublicController extends BaseController {
	private $err = array(
		'AUTH_FAIL' => '用户名或密码错误'
	);
    public function index(){
		$this->display('index');
    }
	
	public function login() {
		if ($_POST) {
			$json = array('error'=>'');
			
			$username = I('post.username');
			$password = I('post.password');
			$AccountEvent = A('Account', 'Event');
			$user = $AccountEvent->auth($username, $password);
			if (!$user) {
				$json['error'] = $this->err['AUTH_FAIL'];
				
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
			
			$setting = M('Setting')->where(array('city_id'=>$user['city_id']))->select();
			foreach($setting as $row) {
				if ($row['serialized'] == 1) {
					$setting[$row['key']] = unserialize($row['value']);
				}else{
					$setting[$row['key']] = $row['value'];
				}
			}
			
			$change_pwd = false;
			if (isset($setting['config_change_pwd_alert']) && $setting['config_change_pwd_alert'] == 1) {
				if (empty($user['change_pwd_time'])) {
					$change_pwd = true;
				}else {
					$dt1 = new \DateTime($user['change_pwd_time']);
					$dt2 = new \DateTime('now');
					$interval = $dt1->diff($dt2);
					$n = $interval->format('%a');
					if (isset($setting['config_change_pwd_interval']) && $n >= $setting['config_change_pwd_interval']) {
						$change_pwd = true;
					}
				}
			}
			if ($change_pwd) {
				$json['chng_alert'] = 1;
				$json['redirect'] = U('Account/pwd');
			}else {
				$json['redirect'] = U('Issue/index');
			}
			exit(json_encode($json));
		}
	}
	
	public function detectLogin() {
		if (!session('?user') || !session('?city')) {
			session('user', null);
			session('city', null);
			$this->redirect('Home/Public/index');
		}
	}

}