<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends GlobalController {
    public function index(){
		$this->assign('title', '用户管理');
		
		$this->display();
    }
	
	public function add() {
		if ($_POST) {
			$username = I('post.username', '');
			if (empty($username)) {
				$this->error('用户名不能为空', 'javascript:history.back(-1);', 5);
			}
			
			$d = M('User')->where(array('username'=>$username))->find();
			if (!empty($d)) {
				$this->error('该用户名已被使用', 'javascript:history.back(-1);', 5);
			}
			
			$data = array(
				'username' => $username,
				'password' => md5encode(I('post.newpwd')),
				'smartphone' => I('post.smartphone'),
				'user_type_id' => I('post.user_type_id'),
				'city_id' => I('post.city'),
				'target' => I('post.target'),
				'area' => I('post.area'),
				'create_time' => date('Y-m-d H:i:s')
			);
			
			$id = M('User')->data($data)->add();
			if ($id > 0) {
				$this->success('添加用户成功', U('Home/User/index'), 3);
			}
			$this->error('添加用户失败', 'javascript:history.back(-1);', 5);
		}
		$this->assign('title', '添加用户');
		
		$user_types = M('UserType')->select();
		$this->assign('user_types', $user_types);
		
		$AreaEvent = A('Area', 'Event');
		$provinces = $AreaEvent->getProvinceList();
		$this->assign('provinces', $provinces);
		
		$this->assign('sel_city', $this->city);
		$this->display();
	}
	
	public function jsondata() {
		$page = I('post.page', 1);
		$rows = I('post.rows', 20);
		$sort = I('post.sort', 'username');
		$order_by = I('post.order', 'ASC');
		
		$username = I('post.username', '');
		$smartphone = I('post.smartphone', '');
		$city_id = I('post.city_id', '');
		
		$cond = array();
		empty($username) || $cond['u.username'] = array('like', $username.'%');
		empty($smartphone) || $cond['u.smartphone'] = $smartphone;
		empty($city_id) || $cond['u.city_id'] = $city_id;
		
		//empty($cond) || $cond['_logic']='or';
		
		$total = M('User')->alias('u')
			->where($cond)->count();
		
		$start = ($page - 1) * $rows;
		$list = M('User')->alias('u')
			->field('
				u.*, 
				ut.type_name AS user_type_name,
				c.city AS city_name')
			->join('INNER JOIN user_type ut ON u.user_type_id=ut.user_type_id')
			->join('INNER JOIN city c ON u.city_id=c.city_id')
			->where($cond)
			->order($sort . ' ' . $order_by)
			->limit($start, $rows)
			->select();
			
		echo $this->generateDataForDataGrid($total, $list);
	}
	
	public function checkUsername() {
		$json = true;
		
		$username = I('post.username', '');
		if (empty($username)) {
			$json = '用户名不能为空';
			$this->ajaxReturn($json);
		}
		
		$d = M('User')->where(array('username'=>$username))->find();
		if (!empty($d)) $json = false;
		
		$this->ajaxReturn($json);
	}
}