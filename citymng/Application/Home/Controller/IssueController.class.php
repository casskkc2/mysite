<?php
namespace Home\Controller;
use Think\Controller;
class IssueController extends GlobalController {
	function _initialize(){
		parent::_initialize();
		
	}
    public function index(){
		$status_id = I('get.st', 0);
		
		$IssueEvent = A('Issue', 'Event');
		$tabs = $IssueEvent->getTabs($this->user['user_type_id']);//print_r($tabs);exit;
		
		if (empty($status_id)) {
			$arr = array_slice($tabs, 0, 1);
			//var_dump($arr);exit;
			$status_id = $arr[0]['st'];
		}
		
		$tools = $IssueEvent->getPassBtns($status_id, $this->user['user_type_id']);
		$this->assign('tabs', $tabs);
		$this->assign('tools', $tools);
		
		$AreaEvent = A('Area', 'Event');
		$area_list = $AreaEvent->getAreaList($this->city['city_id'], 0, $this->all_area_arr);
		$this->assign('area_list', $area_list);
		
		$TargetEvent = A('Target', 'Event');
		$target_list = $TargetEvent->getTargetList($this->city['city_id'], 0, $this->all_target_arr);
		$this->assign('target_list', $target_list);
		
		$this->assign('hourList', $IssueEvent->getHours());
		$this->assign('minuteList', $IssueEvent->getMinutes());
		
		$user_list = array();
		if ($this->user['user_type_id'] == 20) {
			$cond = array(
				'city_id' => $this->city['city_id'],
				'user_type_id' => 21
			);
			$user_list = M('User')->where($cond)->order('id')->select();
		}
		$this->assign('user_list', $user_list);
		
		$this->assign('title', '问题管理');
		$this->assign('status', $status_id);
		$this->assign('self_url', U('Issue/index', '', ''));
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
				'title' => '',//I('post.title'),
				'checker' => I('post.checker'),
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
				$data['title'] .= $a_n;
			}
			$target_names = I('post.target_name');
			$target_last_name = '';
			foreach($target_names as $k=>$a_n){
				$data['tags'] .= $a_n . '|';
				if ($k > 2) break; // max is $target3
				$data['target' . ($k+1)] = $a_n;
				$target_last_name = $a_n;
			}
			$data['title'] .= $target_last_name;
			
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
					$file_info = $this->_upload2($file, false, 0, 0, '');
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
			
			if(!empty($upload_error)) {
				foreach($replies as $v) {
					if ($v['type'] == 2) {
						@unlink($v['path']);
					}
				}
				$err_no = 0;
				$err_msg = '';
				foreach($upload_error as $k=>$err) {
					$err_no = $k;
					$err_msg = $err;
					break;
				}
				$this->error('第' . $err_no . '个附件上传失败:' . $err_msg, 'javascript:history.back(-1);', 5);
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
		$area_list = $AreaEvent->getAreaList($this->city['city_id'], 0, $this->all_area_arr);
		$this->assign('area_list', $area_list);
		
		$TargetEvent = A('Target', 'Event');
		$target_list = $TargetEvent->getTargetList($this->city['city_id'], 0, $this->all_target_arr);
		$this->assign('target_list', $target_list);
		
		$this->display();
	}
	
	public function getAreaList() {
		$pid = I('post.pid');
		$sel_id = I('post.sel_id');
		
		if (empty($pid)) exit('');
		
		$AreaEvent = A('Area', 'Event');
		$area_list = $AreaEvent->getAreaList($this->city['city_id'], $pid, $this->all_area_arr);
		
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
		$target_list = $TargetEvent->getTargetList($this->city['city_id'], $pid, $this->all_target_arr);
		
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
				'title' => '',//I('post.title'),
				'checker' => I('post.checker'),
				'des' => I('post.des'),
				'last_mod_user_id' => $this->user['id']
			);
			
			$area_names = I('post.area_name');
			foreach($area_names as $k=>$a_n){
				$data['tags'] .= $a_n . '|';
				if ($k > 3) break; // max is $area4
				$data['area' . ($k+1)] = $a_n;
				$data['title'] .= $a_n;
			}
			$target_names = I('post.target_name');
			$target_last_name = '';
			foreach($target_names as $k=>$a_n){
				$data['tags'] .= $a_n . '|';
				if ($k > 2) break; // max is $target3
				$data['target' . ($k+1)] = $a_n;
				$target_last_name = $a_n;
			}
			$data['title'] .= $target_last_name;
			
			$target = M('Target')->where(array('id'=>$data['target_id']))->find();
			if (!empty($target)) {
				$data['target_code'] = $target['code'];
				$data['tags'] .= $target['code'] . '|';
			}
			
			$from_page_status = I('post.from_page_status', '');
			
			$stat = M('Issue')->data($data)->save();
			if (false !== $stat) {
				if (!empty($from_page_status)) {
					$this->success('修改问题成功', U('Home/Issue/index', array('st'=>$from_page_status)), 3);
				}else {
					$this->success('修改问题成功', U('Home/Issue/index'), 3);
				}
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
		$this->assign('from_page_status', I('get.status', ''));
		
		$AreaEvent = A('Area', 'Event');
		$area_list = $AreaEvent->getAreaList($this->city['city_id'], 0, $this->all_area_arr);
		$this->assign('area_list', $area_list);
		
		$TargetEvent = A('Target', 'Event');
		$target_list = $TargetEvent->getTargetList($this->city['city_id'], 0, $this->all_target_arr);
		$this->assign('target_list', $target_list);
		
		$this->assign('title', '编辑问题');
		$this->display('Issue/add');
	}
	
	public function advancedSearch() {
		if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
			$data = array();
			$data['page'] = I('post.page', 1);
			$data['rows'] = I('post.rows', 20);
			$data['sort'] = I('post.sort', 'id');
			$data['order_by'] = I('post.order', 'ASC');
			
			$data['city_id'] = $this->city['city_id'];
			$data['advance'] = true;
			
			$id = I('post.id', 0);
			$status_id = I('post.status_id', 0);
			$area_id = I('post.area_id', 0);
			$target_id = I('post.target_id', 0);
			$area_names = I('post.area_names', '');
			$target_names = I('post.target_names', '');
			$start_date = I('post.start_date', '');
			$end_date = I('post.end_date', '');
			!empty($id) && $data['id'] = $id;
			!empty($status_id) && $data['status_id'] = $status_id;
			!empty($start_date) && $data['exm_start_date'] = $start_date;
			!empty($end_date) && $data['exm_end_date'] = $end_date;
			
			if (!empty($area_id)) {
				$data['area_id'] = $area_id;
			}else if (!empty($area_names)) {
				$arr = explode(',', $area_names);
				foreach($arr as $k=>$v) {
					$data['area' . ($k+1)] = $v;
				}
			}
			
			if (!empty($target_id)) {
				$data['target_id'] = $target_id;
			}else if (!empty($target_names)) {
				$arr = explode(',', $target_names);
				foreach($arr as $k=>$v) {
					$data['target' . ($k+1)] = $v;
				}
			}
			//print_r($data);exit;
			$IssueEvent = A('Issue', 'Event');
			
			$res = $IssueEvent->getIssueList($data);
				
			echo $this->generateDataForDataGrid($res['total'], $res['data']);
			exit;
		}
		$AreaEvent = A('Area', 'Event');
		$area_list = $AreaEvent->getAreaList($this->city['city_id'], 0, $this->all_area_arr);
		$this->assign('area_list', $area_list);
		
		$TargetEvent = A('Target', 'Event');
		$target_list = $TargetEvent->getTargetList($this->city['city_id'], 0, $this->all_target_arr);
		$this->assign('target_list', $target_list);
		
		$IssueEvent = A('Issue', 'Event');
		$status_list = M('IssueStatus')->select();
		foreach($status_list as $key=>$row) {
			if ($this->user['user_type_id'] >= 20 && in_array($row['status_id'], array(1, 2))) {
				unset($status_list[$key]);
				continue;
			}
			$status_list[$key]['name'] = $IssueEvent->getStatusName($row['status_id'], $this->user['user_type_id'], $row['name']);
		}
		$this->assign('status_list', $status_list);
		
		$this->assign('title', '高级搜索');
		$this->display('Issue:search');
	}
	
	public function jsondata() {
		$data = array();
		$data['page'] = I('post.page', 1);
		$data['rows'] = I('post.rows', 20);
		$data['sort'] = I('post.sort', 'id');
		$data['order_by'] = I('post.order', 'ASC');
		
		$data['city_id'] = $this->city['city_id'];
		
		$user_id = I('post.user_id', 0);
		$user_info = array();
		if (!empty($user_id)) {
			$user_info = M('User')->where(array('id'=>$user_id))->find();
		}
		//if ( !in_array($this->user['user_type_id'], array(10, 20)) ) {
		if (empty($user_info)) {
			$data['area'] = $this->user['area'];
			$data['target'] = $this->user['target'];
		}
		//}
		else {
			// search by the specified user
			$data['area'] = $user_info['area'];
			$data['target'] = $user_info['target'];
		}
		
		$keywords = I('post.keywords', '');
		//$keywords = I('get.keywords', '');
		$start_date = I('post.start_date', '');
		$end_date = I('post.end_date', '');
		
		$start_hour = I('post.start_hour', '');
		$start_minute = I('post.start_minute', '');
		$end_hour = I('post.end_hour', '');
		$end_minute = I('post.end_minute', '');
		$start_time = '';
		$end_time = '';
		if ($start_hour !== '') {
			$start_time .= $start_hour;
			$start_time .= ':' . (($start_minute !== '') ? $start_minute : '00');
			$start_time .= ':00';
		}
		if ($end_hour !== '') {
			$end_time .= $end_hour;
			$end_time .= ':' . (($end_minute !== '') ? $end_minute : '00');
			$end_time .= ':00';
		}
		
		!empty($keywords) && $data['keywords'] = $keywords;
		!empty($start_date) && $data['exm_start_date'] = $start_date;
		!empty($end_date) && $data['exm_end_date'] = $end_date;
		!empty($start_time) && $data['exm_start_time'] = $start_time;
		!empty($end_time) && $data['exm_end_time'] = $end_time;
		
		$IssueEvent = A('Issue', 'Event');
		$status = I('post.status', 0);
		if(!empty($status)) {
			$IssueEvent->statusToQueryCondition($status, $data);
			if (in_array($status, array(2, 21, 22))) {
				$data['order'] = 'DESC';
			}
		}
		
		$res = $IssueEvent->getIssueList($data);
			
		echo $this->generateDataForDataGrid($res['total'], $res['data']);
	}
	
	public function doCheck() { // pass or nopass
		$json = array(
			'status' => true,
			'error'	=> ''
		);
		$ids = I('post.ids', '');
		$mode = I('post.mode');
		
		if ($mode == 'pass') {
			$data = array(
				'status_id' 	=> 3,
				'start_date' 	=> date('Y-m-d'),
				'end_date'		=> date('Y-m-d', strtotime('+' . $this->setting['config_exp_days'] . ' days'))
			);
		}else if ($mode == 'nopass') {
			$data = array(
				'status_id' 	=> 2,
				'start_date' 	=> NULL,
				'end_date'		=> NULL
			);
		}else if ($mode == 'setvp') {
			$data = array(
				'is_vp' => 1
			);
		}else {
			$json['status'] = false;
			$this->ajaxReturn($json);
		}
		
		$cond = array(
			'id' => array('in', explode(',', $ids))
		);
		$data['last_mod_user_id'] = $this->user['id'];
		$stat = M('Issue')->where($cond)->data($data)->save();
		if (false === $stat) {
			$json['status'] = false;
		}
		$this->ajaxReturn($json);
	}
	
	public function reply() {
		$id = I('post.id');
		//var_dump($id);exit;
		$operation_id = I('post.operation_id', 0);
		$reply_text = I('post.reply_text', '');
		
		$IssueEvent = A('Issue', 'Event');
		$info = $IssueEvent->getIssueDetail($id);
		if (empty($info)) {
			echo '<script>parent.alert("参数错误");</script>';
			exit;
		}
		
		if (!empty($operation_id)) {
			$new_status_id = $IssueEvent->doOperation($operation_id, $this->user, $info, $this->setting);
			if (false === $new_status_id) {
				echo '<script>parent.alert("操作失败");</script>';
				exit;
			}
			if ($new_status_id > 0) {
				$status = M('IssueStatus')->where(array('status_id'=>$new_status_id))->find();//dump($status);
				if (!empty($status)) {
					$status['name'] = $IssueEvent->getStatusName($status['status_id'], $this->user['user_type_id'], $status['name']);
					$reply_text = '[' . $status['name'] . ']' . $reply_text;
				}
			}
		}
		if (!empty($reply_text)) {
			$data = array(
				'issue_id'	=> $info['id'],
				'type'		=> 4,
				'text'		=> $reply_text,
				'user_id'	=> $this->user['id'],
				'create_time'	=> date('Y-m-d H:i:s')
			);
			$stat = M('IssueReply')->data($data)->add();
			if (false === $stat) {
				echo '<script>parent.alert("回复失败");</script>';
				exit;
			}
		}
		if (!empty($_FILES['attachment']['tmp_name'])) {
			$file = $_FILES['attachment'];
			
			$file_info = $this->_upload2($file, false, 0, 0, 'no_limit');
			if (is_array($file_info)) { // upload successfully
				$data = array(
					'issue_id'	=> $info['id'],
					'type'		=> 1,
					'user_id'	=> $this->user['id'],
					'create_time'	=> date('Y-m-d H:i:s'),
					'path' => $file_info['file_path']
				);
				$stat = M('IssueReply')->data($data)->add();
				if (false === $stat) {
					@unlink($file_info['file_path']);
					echo '<script>parent.alert("附件上传失败!");</script>';
					exit;
				}
			}else {
				echo '<script>parent.alert("附件上传失败");</script>';
				exit;
			}
		}
		
		$html = '<script>parent.alert("操作成功"); parent.detail(' . $info['id'] . ');';
		if (!empty($operation_id)) {
			$html .= 'parent.document.getElementById("btn_search").click();';
		}
		$html .= '</script>';
		echo $html;
		exit;
	}
	
	public function detail() {
		$id = I('post.id');
		$from = I('post.from', '');
		
		$IssueEvent = A('Issue', 'Event');
		$info = $IssueEvent->getIssueDetail($id);
		$info['status_name'] = $IssueEvent->getStatusName($info['status_id'], $this->user['user_type_id'], $info['status_name']);
		$info['last_mod_user'] = '';
		$info['last_mod_user2'] = '';
		if (!empty($info['last_mod_user_id'])) {
			$info['last_mod_user'] = M('User')->getFieldById($info['last_mod_user_id'], 'username');
		}
		if (!empty($info['last_mod_user2_id'])) {
			$info['last_mod_user2'] = M('User')->getFieldById($info['last_mod_user2_id'], 'username');
		}
		
		$this->assign('info', $info);
		$this->assign('current_user', $this->user);
		
		$reply_list = $IssueEvent->getReplyList($id,'text');
		$attachment_list = $IssueEvent->getReplyList($id,'attachment');//dump($attachment_list);exit;
		$this->assign('reply_list', $reply_list);
		$this->assign('attachment_list', $attachment_list);
		
		if($from == 'list_page') {
			$can_edit = $IssueEvent->canEdit($info['status_id'], $this->user['user_type_id']);
			$can_reply = $IssueEvent->canReply($info['status_id'], $this->user['user_type_id']);
			$status_operations = $IssueEvent->getStatusOperations($info['status_id'], $this->user['user_type_id']);
			$this->assign('can_edit', $can_edit);
			$this->assign('can_reply', $can_reply);
			$this->assign('status_operations', $status_operations);
			
			$res = array();
			$res['detail'] = $this->fetch('Issue:detail');
			$res['reply'] = $this->fetch('Issue:reply');
			$res['pos'] = array('city'=>$this->city['city'], 'lat'=>$info['lat'], 'lng'=>$info['lng']);
			$this->ajaxReturn($res);
		}else {
			
			$this->display('Issue:info');
		}
	}
	
	public function doprint() {
		$id = I('get.id', 0);
		if(empty($id)) $this->error('参数错误');
		
		$IssueEvent = A('Issue', 'Event');
		$info = $IssueEvent->getIssueDetail($id);
		$this->assign('info', $info);
		
		$reply_list = $IssueEvent->getReplyList($id,'text');
		$attachment_list = $IssueEvent->getReplyList($id,'attachment');//dump($attachment_list);exit;
		$this->assign('reply_list', $reply_list);
		$this->assign('attachment_list', $attachment_list);
		
		$this->display();
	}
	
	public function export() {
		$mode = I('get.mode', 'default');
		$status = I('get.status', 0);
		$filter_date_start = I('get.filter_date_start', '');
		$filter_date_end = I('get.filter_date_end', '');
		$keywords = I('get.keywords', '');
		
		$start_hour = I('get.filter_hour_start', '');
		$start_minute = I('get.filter_minute_start', '');
		$end_hour = I('get.filter_hour_end', '');
		$end_minute = I('get.filter_minute_end', '');
		$start_time = '';
		$end_time = '';
		if ($start_hour !== '') {
			$start_time .= $start_hour;
			$start_time .= ':' . (($start_minute !== '') ? $start_minute : '00');
			$start_time .= ':00';
		}
		if ($end_hour !== '') {
			$end_time .= $end_hour;
			$end_time .= ':' . (($end_minute !== '') ? $end_minute : '00');
			$end_time .= ':00';
		}
		
		$cond = array();
		$cond['city_id'] = $this->city['city_id'];
		
		/*if ( !in_array($this->user['user_type_id'], array(10, 20)) ) {
			$cond['area'] = $this->user['area'];
			$cond['target'] = $this->user['target'];
		}*/
		$user_id = I('get.user_id', 0);
		$user_info = array();
		if (!empty($user_id)) {
			$user_info = M('User')->where(array('id'=>$user_id))->find();
		}
		if (empty($user_info)) {
			$cond['area'] = $this->user['area'];
			$cond['target'] = $this->user['target'];
		}
		else {
			// search by the specified user
			$cond['area'] = $user_info['area'];
			$cond['target'] = $user_info['target'];
		}
		
		!empty($keywords) && $cond['keywords'] = $keywords;
		!empty($filter_date_start) && $cond['exm_start_date'] = $filter_date_start;
		!empty($filter_date_end) && $cond['exm_end_date'] = $filter_date_end;
		!empty($start_time) && $cond['exm_start_time'] = $start_time;
		!empty($end_time) && $cond['exm_end_time'] = $end_time;
		
		$IssueEvent = A('Issue', 'Event');
		if(!empty($status)) {
			$IssueEvent->statusToQueryCondition($status, $cond);
		}
		
		$res = $IssueEvent->getIssueList($cond);
		$data = $res['data'];
		
		$cols = array(
			'编号',
			'日期', '时间',
			'区域', '类别', '街道', '道路名称',
			'来源', 'checker'=>'发现人', '指标代码',
			'一级指标', '二级指标', '三级指标',
			'详细信息', '问题个数', '状态', 'username'=>'录入员',
			'照片'
		);
		
		if ($this->user['user_type_id'] >= 20) {
			unset($cols['checker']);
			unset($cols['username']);
		}
		
		if ($mode == 'default' || $mode == 'with_img') {
			$rows = array();
			foreach($data as $k=>$row) {
				$img = '';
				!empty($row['img']) && $img = C('HTTP_SERVER') . $row['img'];
				$img_value = '';
				!empty($img) && $img_value = array('text'=>$img, 'url'=>$img);
				
				$user = M('User')->where(array('id'=>$row['user_id']))->find();
				
				if (empty($row['area4'])) {
					$row['area4'] = $row['area3'];
					$row['area3'] = '';
				}
				
				$row['status_name'] = $IssueEvent->getStatusName($row['status_id'], $this->user['user_type_id'], $row['status_name']);
				
				$tmp = array(
					$row['id'],
					$row['date'], $row['time'], 
					$row['area1'],$row['area2'],$row['area3'],$row['area4'],
					$row['come_from'], 'checker'=>$row['checker'], $row['target_code'],
					$row['target1'],$row['target2'],$row['target3'],
					$row['des'],  $row['weight'], $row['status_name'], 'username'=>(!empty($user['username']) ? $user['username'] : ''),
					($mode == 'default' ? $img_value : $row['img'])
				);
				if ($this->user['user_type_id'] >= 20) {
					unset($tmp['checker']);
					unset($tmp['username']);
				}
				$rows[] = $tmp;
			}
			$ExcelEvent = A('Excel', 'Event');
			$ExcelEvent->export($cols, $rows, '问题导出' . date('YmdHi'), 'issue_' . $mode);
		}else if ($mode == 'only_img') {
			$num = 0;
			foreach($data as $row) {
				if (empty($row['img']) || !file_exists($row['img'])) continue;
				$num++;
			}
			if ($num == 0) {
				header('Content-Type: text/html;charset=utf-8');
				exit('没有符合条件的图片');
			}
			
			$fname = '图片导出' . $this->user['id'] . date('ymdHis') . '.zip';
			$zip_file = C('DOWNLOAD_DIR') . $fname;
			$zip_file_gbk = iconv('UTF-8', 'GBK', $zip_file);

			$zip = new \ZipArchive;
			if (($res = $zip->open($zip_file_gbk, \ZipArchive::OVERWRITE)) !== TRUE)  {
				exit('create zip file failed. Error code: ' . $res);
			}
			foreach($data as $row) {
				if (empty($row['img']) || !file_exists($row['img'])) continue;
				
				$ext = strrchr($row['img'], '.');
				$new_name = $row['new_title'] . $ext;
				$new_name = iconv('UTF-8', 'GBK', $new_name);
		
				$zip->addFile($row['img'], $new_name);
			}
			$zip->close();
			header('Location: ' . C('HTTP_SERVER') . $zip_file);
		}
	}
	
	public function importIssues() {
		//echo $_SERVER['REQUEST_METHOD'];exit;
		if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
			$error = '';
				
			$file = $_FILES['file'];
			$file_info = $this->_upload2($file, false, 0, 0, 'xls|xlsx');
			if (is_array($file_info)) { // upload successfully
				$fpath = $file_info['file_path'];
				
				// todo
				$ExcelEvent = A('Excel', 'Event');
				$res = $ExcelEvent->importIssue($fpath, $this->user, $this->city);
				$this->assign('res', $res);
				@unlink($fpath);
			}else {
				$error = $file_info;
			}
			
			$this->assign('error', $error);
			$this->display('Issue:importResult');exit;
		}
		$this->assign('title', '批量上报');
		$this->display();
	}
	
	public function exportSummary() {
		$root_area_ids = I('get.area', '');
		$start_date = I('get.filter_date_start', '');
		$end_date = I('get.filter_date_end', '');
		$root_target_ids = I('get.target', '');
		$status = I('get.status', 0);
		
		if (empty($root_area_ids) || empty($root_target_ids)) {
			$this->error('请选择区、指标'); exit;
		}
		
		$arr = explode(',', $root_area_ids);
		$AreaEvent = A('Area', 'Event');
		$area_list = $AreaEvent->getAreaAndChildrenByIds($this->city['city_id'], $arr, $this->all_area_arr);
		//var_export($area_list);
		$area_data = buildTree($area_list);
		//var_export($area_data);exit;
		//getSummaryColsFromTreeData($area_data);
		//var_export($area_data);//exit;
		//$area_max = getMaxDimensionOfTreeData($area_data);
		//echo $area_max;exit;
		$leaf_area = getLeafNodesFromTreeData($area_data);
		//var_export($leaf_area); exit;
		$leaf_area_id_arr = array_map(function($v) {
			return $v['id'];
		}, $leaf_area);
		//var_export($leaf_area_id_arr); exit;
		
		$arr = explode(',', $root_target_ids);
		$TargetEvent = A('Target', 'Event');
		$target_list = $TargetEvent->getTargetAndChildrenByIds($this->city['city_id'], $arr, $this->all_target_arr);
		//$cond = array('city_id'=>$this->city['city_id']);
		//$target_list = M('Target')->where($cond)->order('path, sort')->select();
		//var_export($target_list);exit;
		$target_data = buildTree($target_list);
		$target_data[] = array(
			'id'	=> 'col_smry',
			'text'	=> '合计'
		);
		//var_export($target_data);exit;
		getSummaryColsFromTreeData($target_data);
		//var_export($target_data);exit;
		$target_max = getMaxDimensionOfTreeData($target_data);
		//echo $target_max;exit;
		$leaf_target = getLeafNodesFromTreeData($target_data);
		//var_export($leaf_target); exit;
		$leaf_target_id_arr = array_map(function($v){
			return $v['id'];
		}, $leaf_target);
		//var_export($leaf_target_id_arr); exit;
		
		$where = array(
			'city_id'	=> $this->city['city_id'],
			'area_id'	=> array('in', $leaf_area_id_arr),
			'target_id'	=> array('in', $leaf_target_id_arr)
		);
		$examine_time_cond = array();
		!empty($end_date) && $examine_time_cond[]= array('elt', $end_date . ' 23:59:59');
		!empty($start_date) && $examine_time_cond[]= array('egt', $start_date);
		!empty($examine_time_cond) && $where['examine_time'] = $examine_time_cond;
		
		$IssueEvent = A('Issue', 'Event');
		if(!empty($status)) {
			$IssueEvent->statusToQueryCondition($status, $where);
		}
		
		$data = array();
		$list = M('Issue')
			->field('SUM(weight) AS sum_weight, area_id, target_id')
			->where($where)
			->group('area_id, target_id')
			->select();
		foreach($list as $row) {
			$data[$row['area_id']][$row['target_id']] = $row['sum_weight'];
			if (isset($data[$row['area_id']]['col_smry'])) {
				$data[$row['area_id']]['col_smry'] += $row['sum_weight'];
			}else {
				$data[$row['area_id']]['col_smry'] = $row['sum_weight'];
			}
		}
		//var_export($data);
		$smry_total = array();
		foreach($area_data as $key=>$value) { // add sum for each Area(the 1st dimension)
			if (!empty($value['children'])) {
				$id = 'smry_' . $value['id'];
				$area_data[$key]['children'][] = array(
					'id' => $id,
					'is_smry'	=> true,
					'text'	=> '小计'
				);
				$data[$id] = $this->_getSumForEachArea($value['children'], $data);
				$smry_total = array_merge_sum($smry_total, $data[$id]);
			}
		}
		$area_data[] = array(
			'id'	=> 'smry_total',
			'is_total'	=> true,
			'text'	=> '总计'
		);
		$data['smry_total'] = $smry_total;
		
		//var_export($data);exit;
		//var_export($area_data);exit;
		getSummaryColsFromTreeData($area_data);
		//var_export($area_data);exit;
		$area_max = getMaxDimensionOfTreeData($area_data);
		//echo $area_max;exit;
		
		$ExcelEvent = A('Excel', 'Event');
		$ExcelEvent->exportSummary($area_data, $target_data, $area_max, $target_max, $data, '导出汇总' . date('YmdHi'));
	}
	
	private function _getSumForEachArea($children, $data) { // only for the 1st dimension area
		$res = array();
		foreach($children as $key=>$child) {
			if (empty($child['children'])) {
				if (isset($data[$child['id']])) {
					$res = array_merge_sum($res, $data[$child['id']]);
				}
			}else {
				$res = array_merge_sum($res, $this->_getSumForEachArea($child['children'], $data));
			}
		}
		return $res;
	}
	
	// just for super admin
	public function superMng() {
		if ($this->user['user_type_id'] != 19) {
			$this->error('无权限访问');
		}
		/*
		if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
			$data = array();
			$data['page'] = I('post.page', 1);
			$data['rows'] = I('post.rows', 20);
			$data['sort'] = I('post.sort', 'id');
			$data['order_by'] = I('post.order', 'ASC');
			
			$data['city_id'] = $this->city['city_id'];
			$data['advance'] = true;
			
			$id = I('post.id', 0);
			$status_id = I('post.status_id', 0);
			$area_id = I('post.area_id', 0);
			$target_id = I('post.target_id', 0);
			$area_names = I('post.area_names', '');
			$target_names = I('post.target_names', '');
			$start_date = I('post.start_date', '');
			$end_date = I('post.end_date', '');
			!empty($id) && $data['id'] = $id;
			!empty($status_id) && $data['status_id'] = $status_id;
			!empty($start_date) && $data['exm_start_date'] = $start_date;
			!empty($end_date) && $data['exm_end_date'] = $end_date;
			
			if (!empty($area_id)) {
				$data['area_id'] = $area_id;
			}else if (!empty($area_names)) {
				$arr = explode(',', $area_names);
				foreach($arr as $k=>$v) {
					$data['area' . ($k+1)] = $v;
				}
			}
			
			if (!empty($target_id)) {
				$data['target_id'] = $target_id;
			}else if (!empty($target_names)) {
				$arr = explode(',', $target_names);
				foreach($arr as $k=>$v) {
					$data['target' . ($k+1)] = $v;
				}
			}
			//print_r($data);exit;
			$IssueEvent = A('Issue', 'Event');
			
			$res = $IssueEvent->getIssueList($data);
				
			echo $this->generateDataForDataGrid($res['total'], $res['data']);
			exit;
		}*/
		$AreaEvent = A('Area', 'Event');
		$area_list = $AreaEvent->getAreaList($this->city['city_id'], 0);
		$this->assign('area_list', $area_list);
		
		$TargetEvent = A('Target', 'Event');
		$target_list = $TargetEvent->getTargetList($this->city['city_id'], 0);
		$this->assign('target_list', $target_list);
		
		$IssueEvent = A('Issue', 'Event');
		$status_list = M('IssueStatus')->select();
		foreach($status_list as $key=>$row) {
				$status_list[$key]['name'] = $IssueEvent->getStatusName($row['status_id'], $this->user['user_type_id'], $row['name']);
		}
		$this->assign('status_list', $status_list);
		
		$this->assign('title', '数据管理');
		$this->display('Issue:superMng');
	}
	public function superCheck() {
		$json = array(
			'status' => true,
			'error'	=> ''
		);
		$ids = I('post.ids', '');
		$mode = I('post.mode');
		
		if ($mode != 'delete') {
			$json['status'] = false;
			$json['error'] = '参数错误';
			$this->ajaxReturn($json);
		}
		
		$cond = array(
			'id' => array('in', explode(',', $ids))
		);
		$flag = true;
		M('Issue')->startTrans();
		
		$stat = M('Issue')->where($cond)->delete();
		if (false === $stat) {
			$flag = false;
		}
		
		if ($flag) {
			$cond = array(
				'issue_id' => array('in', explode(',', $ids))
			);
			$stat = M('IssueReply')->where($cond)->delete();
			if (false === $stat) {
				$flag = false;
			}
		}
		
		if ($flag) {
			M('Issue')->commit();
		}else {
			M('Issue')->rollback();
			$json['status'] = false;
		}
		
		$this->ajaxReturn($json);
	}
}