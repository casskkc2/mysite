<?php
namespace Home\Controller;
use Think\Controller;
class IssueController extends GlobalController {
	function _initialize(){
		parent::_initialize();
		
	}
    public function index(){
		$this->assign('title', '问题管理');
		$this->display();
    }
	
	public function add() {
		if ($_POST) {
			$data = array(
				'area_id' => I('post.area_id'),
				'tags' => '',
				'target_id' => I('post.target_id'),
				'city_id' => $this->city['city_id'],
				'examine_time' => date("Y-m-d H:i:s", strtotime(I('post.examine_time'))),
				'weight' => I('post.weight'),
				'title' => I('post.title'),
				'des' => I('post.des'),
				'user_id' => $this->user['id'],
				'status_id' => 1,
				'create_time' => date("Y-m-d H:i:s")
			);
			$area_names = I('post.area_name');
			foreach($area_names as $k=>$a_n){
				$data['tags'] .= $a_n . '|';
				if ($k > 3) break; // max is $area4
				$data['area' . ($k+1)] = $a_n;
			}
			$target_names = I('post.target_name');
			foreach($target_names as $k=>$a_n){
				$data['tags'] .= $a_n . '|';
				if ($k > 2) break; // max is $target3
				$data['target' . ($k+1)] = $a_n;
			}
			
			$target = M('Target')->where(array('id'=>$data['target_id']))->find();
			if (!empty($target)) {
				$data['target_code'] = $target['code'];
				$data['tags'] .= $target['code'] . '|';
			}
			
			$reply_videos = I('post.reply_video');
			
			$replies = array();
			$reply_types = I('post.reply_type');
			$img_i = 0;
			$video_i = 0;
			$upload_error = array();
			foreach($reply_types as $type) {
				if ($type == 2) {
					$file = array(
						'name' => $_FILES['reply_img']['name'][$img_i],
						'type' => $_FILES['reply_img']['type'][$img_i],
						'tmp_name' => $_FILES['reply_img']['tmp_name'][$img_i],
						'error' => $_FILES['reply_img']['error'][$img_i],
						'size' => $_FILES['reply_img']['size'][$img_i],
					);
					$file_info = $this->_upload2($file);
					if (is_array($file_info)) { // upload successfully
						if ($img_i == 0) {
							$data['img'] = $file_info['file_path'];
						}
						$replies[] = array(
							'type' => $type,
							'path' => $file_info['file_path']
						);
					}else {
						$upload_error[$img_i + 1] = $file_info;
					}
					$img_i++;
				}else {
					$replies[] = array(
						'type' => $type,
						'path' => C('UPLOAD_DIR') . $reply_videos[$video_i]
					);
					$video_i++;
				}
			}
			
			$id = M('Issue')->data($data)->add();
			if (!$id) {
				$this->error('上报问题失败', 'javascript:history.back(-1);', 5);
			}
			foreach($replies as $reply) {
				$r_data = array(
					'issue_id' => $id,
					'type' => $reply['type'],
					'path' => $reply['path'],
					'create_time' => $data['create_time'],
					'user_id' => $this->user['id']
				);
				$r_id = M('IssueReply')->data($r_data)->add();
			}
			
			if (!empty($upload_error)) {
				$error = '<p style="color:red;">共有' . count($upload_error) . '个图片上传失败：';
				foreach($upload_error as $k=>$err) {
					$error .= '<br />第 ' . $k . ' 个图片上传失败，原因：' . $err;
				}
				$error .= '</p>';
				
				$this->success('问题上报成功' . $error, U('Home/Issue/add'), 30);
			}
			$this->success('问题上报成功', U('Home/Issue/add'), 3);
			/*header('Content-Type:text/html;charset=utf-8');
			dump($data);
			dump($replies);
			dump($upload_error);exit;*/
		}
		$this->assign('title', '问题上报');
		
		$AreaEvent = A('Area', 'Event');
		$area_list = $AreaEvent->getAreaList($this->city['city_id'], 0);
		$this->assign('area_list', $area_list);
		
		$TargetEvent = A('Target', 'Event');
		$target_list = $TargetEvent->getTargetList($this->city['city_id'], 0);
		$this->assign('target_list', $target_list);
		
		$this->display();
	}
	
	public function getAreaList() {
		$pid = I('post.pid');
		$sel_id = I('post.sel_id');
		
		if (empty($pid)) exit('');
		
		$AreaEvent = A('Area', 'Event');
		$area_list = $AreaEvent->getAreaList($this->city['city_id'], $pid);
		
		if (empty($area_list)) exit('');
		
		$this->assign('list', $area_list);
		$this->assign('sel_id', $sel_id);
		
		$this->display('Issue/html_select_options');
	}
	
	public function getTargetList() {
		$pid = I('post.pid');
		$sel_id = I('post.sel_id');
		
		if (empty($pid)) exit('');
		
		$TargetEvent = A('Target', 'Event');
		$target_list = $TargetEvent->getTargetList($this->city['city_id'], $pid);
		
		if (empty($target_list)) exit('');
		
		$this->assign('list', $target_list);
		$this->assign('sel_id', $sel_id);
		
		$this->display('Issue/html_select_options');
	}
	
	public function edit() {
		if($_POST) {
			$data = array(
				'id' => I('post.id'),
				'area_id' => I('post.area_id'),
				'tags' => '',
				'target_id' => I('post.target_id'),
				'city_id' => $this->city['city_id'],
				'examine_time' => date("Y-m-d H:i:s", strtotime(I('post.examine_time'))),
				'weight' => I('post.weight'),
				'title' => I('post.title'),
				'des' => I('post.des'),
				'last_mod_user_id' => $this->user['id']
			);
			
			$area_names = I('post.area_name');
			foreach($area_names as $k=>$a_n){
				$data['tags'] .= $a_n . '|';
				if ($k > 3) break; // max is $area4
				$data['area' . ($k+1)] = $a_n;
			}
			$target_names = I('post.target_name');
			foreach($target_names as $k=>$a_n){
				$data['tags'] .= $a_n . '|';
				if ($k > 2) break; // max is $target3
				$data['target' . ($k+1)] = $a_n;
			}
			
			$target = M('Target')->where(array('id'=>$data['target_id']))->find();
			if (!empty($target)) {
				$data['target_code'] = $target['code'];
				$data['tags'] .= $target['code'] . '|';
			}
			
			$stat = M('Issue')->data($data)->save();
			if (false !== $stat) {
				$this->success('修改问题成功', U('Home/Issue/index'), 3);
			}
			$this->error('修改问题失败', 'javascript:history.back(-1);', 5);
		}
		$id = I('get.id', 0);
		if (empty($id)) $this->error('参数错误', 'javascript:history.back(-1);', 5);
		
		$info = M('Issue')->alias('a')
			->field('a.*, b.path AS area_path, c.path AS target_path')
			->join('area b ON a.area_id=b.id')
			->join('target c ON a.target_id=c.id')
			->where(array('a.id'=>$id))->find();
		if (empty($info)) $this->error('参数错误', 'javascript:history.back(-1);', 5);
		
		$area_id_arr = treePathToArray($info['area_path']);
		$area_id_arr[] = $info['area_id'];
		$target_id_arr = treePathToArray($info['target_path']);
		$target_id_arr[] = $info['target_id'];
		
		$this->assign('target_id_arr', $target_id_arr);
		$this->assign('area_id_arr', $area_id_arr);
		$this->assign('info', $info);
		
		$AreaEvent = A('Area', 'Event');
		$area_list = $AreaEvent->getAreaList($this->city['city_id'], 0);
		$this->assign('area_list', $area_list);
		
		$TargetEvent = A('Target', 'Event');
		$target_list = $TargetEvent->getTargetList($this->city['city_id'], 0);
		$this->assign('target_list', $target_list);
		
		$this->assign('title', '编辑问题');
		$this->display('Issue/add');
	}
	
	public function jsondata() {
		$page = I('post.page', 1);
		$rows = I('post.rows', 20);
		$sort = I('post.sort', 'id');
		$order_by = I('post.order', 'ASC');
		
		$username = I('post.username', '');
		$smartphone = I('post.smartphone', '');
		
		$cond = array(
			'city_id' => $this->city['city_id'],
			'area_id' => array('in', $this->user['area']),
			'target_id' => array('in', $this->user['target'])
		);
		empty($username) || $cond['u.username'] = array('like', $username.'%');
		empty($smartphone) || $cond['u.smartphone'] = $smartphone;
		
		
		//empty($cond) || $cond['_logic']='or';
		
		$total = M('Issue')->alias('a')
			->where($cond)->count();
		
		$start = ($page - 1) * $rows;
		$list = M('Issue')->alias('a')
			->field('a.*')
			->where($cond)
			->order($sort . ' ' . $order_by)
			->limit($start, $rows)
			->select();
		foreach($list as $key=>$row) {
			$ts = strtotime($row['examine_time']);
			$list[$key]['date'] = date('Y-m-d', $ts);
			$list[$key]['time'] = date('H:i', $ts);
		}
			
		echo $this->generateDataForDataGrid($total, $list);
	}
}