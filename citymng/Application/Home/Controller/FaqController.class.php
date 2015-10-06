<?php
namespace Home\Controller;
use Think\Controller;
class FaqController extends GlobalController {
	function _initialize(){
		parent::_initialize();
		if ($this->user['user_type_id'] != 10) {
			$this->error('无权限访问');
		}
	}
	
	public function index() {
		$this->assign('title', '常见问题');
		$this->display();
	}
	
	public function add() {
		if ($_POST) {
			$faq = M('Faq');
			$faq->create();
			$faq->create_time = date('Y-m-d H:i:s');
			$id = $faq->add();
			if ($id > 0) {
				$this->success('添加成功', 'index');
			}else {
				$this->error('添加失败');
			}
		}
		$this->assign('title', '常见问题');
		$this->display('edit');
	}
	
    public function edit(){
		if ($_POST) {
			$id = I('get.id', 0);
			
			$info = M('Faq')->where(array('id'=>$id))->find();
			if (!empty($info)) {
				$faq = M('Faq');
				$faq->create();
				$faq->id = $id;
				$stat = $faq->save();
				if (false === $stat) {
					$this->error('编辑失败', 'javascript:history.back(-1);', 5);
				}
			}else {
				$this->error('参数错误');
			}
			$this->success('编辑成功', U('Faq/index'), 3);
		}
		
		$id = I('get.id', 0);
		$info = M('Faq')->where(array('id'=>$id))->find();
		if (empty($info)) {
			$this->error('参数错误');exit;
		}
		$this->assign('info', $info);
		
		$this->assign('title', '编辑常见问题');
		$this->display();
    }
	
	public function jsondata() {
		$page = I('post.page', 1);
		$rows = I('post.rows', 20);
		$sort = I('post.sort', 'f.sort');
		$order_by = I('post.order', 'ASC');
		
		$total = M('Faq')->alias('f')->count();
		
		$start = ($page - 1) * $rows;
		$list = M('Faq')->alias('f')
			->field('
				f.*, 
				f.id AS aid')
			->order($sort . ' ' . $order_by)
			->limit($start, $rows)
			->select();
			
		echo $this->generateDataForDataGrid($total, $list);
	}
	
	public function doCheck() {
		$json = array(
			'status' => true,
			'error'	=> ''
		);
		$ids = I('post.ids', '');
		$mode = I('post.mode');
		
		if (strcmp($mode, 'delete') != 0) {
			$json['error'] = '参数错误';
			$json['status'] = false;
			$this->ajaxReturn($json);
		}
		
		$cond = array(
			'id' => array('in', explode(',', $ids))
		);
		$stat = M('Faq')->where($cond)->delete();
		if (false === $stat) {
			$json['status'] = false;
		}
		$this->ajaxReturn($json);
	}
}