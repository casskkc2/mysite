<?php
namespace Home\Controller;
use Think\Controller;
class UserController extends GlobalController {
	function _initialize(){
		parent::_initialize();
		if ($this->user['user_type_id'] != 19) {
			$this->error('无权限访问');
		}
	}
    public function index(){
		$this->assign('title', '用户管理');
		
		$AreaEvent = A('Area', 'Event');
		$provinces = $AreaEvent->getProvinceList();
		$this->assign('provinces', $provinces);
		
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
				'smartphone' => I('post.smartphone', ''),
				'department' => I('post.department', ''),
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
		
		$user_types = M('UserType')->order('gtype')->select();
		$this->assign('user_types', $user_types);
		
		$AreaEvent = A('Area', 'Event');
		$provinces = $AreaEvent->getProvinceList();
		$this->assign('provinces', $provinces);
		
		$this->assign('sel_city', $this->city);
		$this->display();
	}
	
	public function edit() {
		if($_POST) {
			$data = array(
				'id' => I('post.id'),
				'smartphone' => I('post.smartphone', ''),
				'department' => I('post.department', ''),
				'user_type_id' => I('post.user_type_id'),
				'city_id' => I('post.city'),
				'target' => I('post.target'),
				'area' => I('post.area')
			);
			$newpwd = I('post.newpwd');
			if (!empty($newpwd)) {
				$data['password'] = md5encode($newpwd);
			}
			
			$id = M('User')->data($data)->save();
			if ($id !== false) {
				$this->success('编辑用户信息成功', U('Home/User/index'), 3);
			}
			$this->error('编辑用户信息失败', 'javascript:history.back(-1);', 5);
		}
		$id = I('get.id', 0);
		if (empty($id)) $this->error('参数错误', 'javascript:history.back(-1);', 5);
		
		$user = M('User')->alias('u')
			->field('u.*, c.father AS province_id')
			->join('LEFT JOIN city c ON u.city_id=c.city_id')
			->where(array('u.id'=>$id))->find();
		if (empty($user)) $this->error('参数错误', 'javascript:history.back(-1);', 5);
		
		$user_types = M('UserType')->order('gtype')->select();
		$this->assign('user_types', $user_types);
		
		$AreaEvent = A('Area', 'Event');
		$provinces = $AreaEvent->getProvinceList();
		$this->assign('provinces', $provinces);
		
		$this->assign('title', '编辑用户信息');
		$this->assign('user', $user);
		$this->display();
	}
	
	public function jsondata() {
		$page = I('post.page', 1);
		$rows = I('post.rows', 20);
		$sort = I('post.sort', 'ut.gtype');
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
	
	public function exportHistory() {
		$filter_date_start = I('get.filter_date_start', '');
		$filter_date_end = I('get.filter_date_end', '');
		
		$cond = array();
		!empty($filter_date_start) && $cond['a.create_time'] = array('egt', $filter_date_start);
		!empty($filter_date_end) && $cond['a.create_time'] = array('elt', $filter_date_end . ' 23:59:59');
		$list = M('LoginHistory')->alias('a')
			->join('user b ON a.user_id=b.id')
			->field('a.*, b.username, b.smartphone')
			->where($cond)
			->order('a.create_time ASC')
			->select();
		$cols = array(
			'用户名', '电话', '登录时间', '登录IP'
		);
		$rows = array();
		foreach($list as $row) {
			$rows[] = array(
				$row['username'], $row['smartphone'], $row['create_time'], $row['ip']
			);
		}
		if (!empty($rows)) {
			$ExcelEvent = A('Excel', 'Event');
			$ExcelEvent->export($cols, $rows, '登录记录' . date('YmdHi'), 'login_record');
		}else {
			$this->error('没有符合条件的登录记录', 'javascript:window.close();', 3);
		}
	}
	
	public function gps() {
		$res = array();
		$id = I('post.id', 0);
		if (empty($id)) $this->ajaxReturn($res);
		
		$cond = array(
			'user_id' => $id,
			'create_time' => array('egt', date('Y-m-d', strtotime('-1 day')))
		);
		$list = M('UserGps')
			->where($cond)
			->order('create_time ASC')
			->select();
		foreach($list as $row) {
			if (!empty($row['lat']) && !empty($row['lng'])) {
				$res[] = $row;
			}
		}
		
		$this->ajaxReturn($res);
	}
}