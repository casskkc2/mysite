<?php
namespace Home\Controller;
use Think\Controller;
class TargetController extends GlobalController {
	function _initialize(){
		parent::_initialize();
		if (!in_array($this->user['user_type_id'], array(10, 19))) {
			$this->error('无权限访问');
		}
	}
    public function index(){
		$AreaEvent = A('Area', 'Event');
		
		$provinces = $AreaEvent->getProvinceList();
		$this->assign('provinces', $provinces);
		
		$this->assign('sel_city', $this->city);
		$this->assign('title', '指标代码管理');
		
		$this->display();
    }
	
	public function getTree() {
		$res = array();
		
		$city_id = I('post.city_id', 430100);
		$filter = I('post.filter', '');
		if (empty($city_id)) {
			$this->ajaxReturn($res);
		}
		
		$cond = array('city_id'=>$city_id, 'status' => 0);
		if ($filter == 'all') unset($cond['status']);
		$list = M('Target')->where($cond)->order('path, sort')->select();
		foreach($list as $key=>$row) {
			if ($row['status'] == 1) {
				$list[$key]['status_text'] = '已删除';
			}else {
				$list[$key]['status_text'] = '正常';
			}
		}
		$res = buildTree($list, ',0,', array('sort', 'status_text', 'status', 'code'));
		
		$city = M('City')->where(array('city_id'=>$city_id))->find();
		$res = array(
			array(
				'id' => 0,
				'text' => $city['city'],
				'children' => $res
			)
		);
		
		$this->ajaxReturn($res);
	}
	
	public function getMaxSortNum() {
		$res = array('sort'=>1);
		$city_id = I('post.city_id', 0);
		if (empty($city_id)) {
			$this->ajaxReturn($res);
		}
		$cond = array('city_id'=>$city_id);
		$sort = M('Target')->where($cond)->max('sort');
		$res['sort'] = $sort + 1;
		$this->ajaxReturn($res);
	}
	
	public function getNode() {
		$res = array();
		
		$id = I('post.id', 0);
		if (empty($id)) {
			$this->ajaxReturn($res);
		}
		$cond = array('id'=>$id);
		$res = M('Target')->where($cond)->find();
		$this->ajaxReturn($res);
	}
	
	public function checkNodeDeletable() {
		$res = array('status'=>0, 'error'=>'');
		
		$id = I('post.id', 0);
		if (empty($id)) {
			$res['error'] = '参数错误';
			$this->ajaxReturn($res);
		}
		$cond = array('path'=>array('like', '%,' . $id . ',%'));
		$count = M('Target')->where($cond)->count();
		if ($count > 0) {
			$res['error'] = '该节点下有子节点，不能删除';
			$this->ajaxReturn($res);
		}
		
		/*$cond = array('target_id'=>$id);
		$count = M('Issue')->where($cond)->count();
		if ($count > 0) {
			$res['error'] = '该节点下有关联数据，不能删除';
		}*/
		
		$this->ajaxReturn($res);
	}
	
	public function deleteNode() {
		$res = array('status'=>0, 'error'=>'');
		
		$id = I('post.id', 0);
		if (empty($id)) {
			$res['error'] = '参数错误';
			$this->ajaxReturn($res);
		}
		
		$cond = array('id'=>$id);
		$data = array('status' => 1);
		$stat = M('Target')->where($cond)->data($data)->save();
		if (false === $stat) {
			$res['error'] = '删除失败';
			$this->ajaxReturn($res);
		}
		$this->ajaxReturn($res);
	}
	
	public function editNode() {
		$res = array('error'=>'', 'data'=>array());
		$data = array();
		
		$id = I('post.id');
		$data['name'] = I('post.name');
		$data['sort'] = I('post.sort');
		$data['code'] = I('post.code');
		$data['status'] = I('post.status');
		if ($id == 0) {
			$data['city_id'] = I('post.city_id');
			$pid = I('post.pid');
			if ($pid == 0) {
				$data['path'] = ',0,';
			}else {
				$parent = M('Target')->where(array('id'=>$pid))->find();
				$data['path'] = $parent['path'] . $pid . ',';
			}
			$new_id = M('Target')->data($data)->add();
			if ($new_id > 0) {
				$res['data'] = $data;
				$res['data']['id'] = $new_id;
			}else {
				$res['error'] = '添加失败';
			}
		}else {
			$stat = M('Target')->where(array('id'=>$id))->data($data)->save();
			if (false === $stat) {
				$res['error'] = '编辑失败';
			}else {
				$res['data'] = $data;
			}
		}
		
		$this->ajaxReturn($res);
	}
}