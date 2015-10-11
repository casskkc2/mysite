<?phpnamespace Home\Event;use Think\Controller;class IssueEvent extends Controller {		public function getIssueList($data) {		$page = isset($data['page']) ? $data['page'] : 0;		$rows = isset($data['rows']) ? $data['rows'] : 0;		$sort = isset($data['sort']) ? $data['sort'] : 'id';		$order_by = isset($data['order']) ? $data['order'] : 'ASC';				$is_advance = isset($data['advance']) ? $data['advance'] : false;				$cond = array();		!empty($data['city_id']) && $cond['city_id'] = $data['city_id'];				if (!$is_advance) {			!empty($data['area']) && $cond['area_id'] = array('in', $data['area']);			!empty($data['target']) && $cond['target_id'] = array('in', $data['target']);		}else {			// to do		}				if (!empty($data['keywords'])) {			$arr = explode(' ', preg_replace('/\s{2,}/', ' ', $data['keywords']));			$id_cond = array();			$tags_cond = array();			foreach($arr as $val) {				$tags_cond[] = array('like', '%' . $val . '%');				$id_cond[] = array('eq', $val);			}			!empty($tags_cond) && $tags_cond[] = 'and';			!empty($id_cond) && $id_cond[] = 'or';						$where = array();			!empty($tags_cond) && $where['tags'] = $tags_cond;			!empty($id_cond) && $where['a.id'] = $id_cond;						if (!empty($where)) {				$where['_logic'] = 'or';				$cond['_complex'] = $where;			}		}		//print_r($cond);exit;		$examine_time_cond = array();		!empty($data['exm_end_date']) && $examine_time_cond[]= array('elt', $data['exm_end_date'] . ' 23:59:59');		!empty($data['exm_start_date']) && $examine_time_cond[]= array('egt', $data['exm_start_date']);		!empty($examine_time_cond) && $cond['examine_time'] = $examine_time_cond;				!empty($data['id']) && $cond['a.id'] = $data['id'];		!empty($data['area_id']) && $cond['area_id'] = $data['area_id'];		!empty($data['target_id']) && $cond['target_id'] = $data['target_id'];		!empty($data['area1']) && $cond['area1'] = $data['area1'];		!empty($data['area2']) && $cond['area2'] = $data['area2'];		!empty($data['area3']) && $cond['area3'] = $data['area3'];		!empty($data['area4']) && $cond['area4'] = $data['area4'];		!empty($data['target1']) && $cond['target1'] = $data['target1'];		!empty($data['target2']) && $cond['target2'] = $data['target2'];		!empty($data['target3']) && $cond['target3'] = $data['target3'];				!empty($data['status_id']) && $cond['a.status_id'] = $data['status_id'];		!empty($data['is_vp']) && $cond['is_vp'] = $data['is_vp'];		!empty($data['end_date']) && $cond['end_date'] = array('lt', $data['end_date']);		//print_r($cond);exit;		$total = M('Issue')->alias('a')			->where($cond)->count();				$list = array();		if ($rows > 0) {			$start = ($page - 1) * $rows;			$list = M('Issue')->alias('a')				->field('a.*, b.name as status_name')				->join('LEFT JOIN issue_status b ON a.status_id=b.status_id')				->where($cond)				->order($sort . ' ' . $order_by)				->limit($start, $rows)				->select();		}else {			$list = M('Issue')->alias('a')				->field('a.*, b.name as status_name')				->join('LEFT JOIN issue_status b ON a.status_id=b.status_id')				->where($cond)				->order($sort . ' ' . $order_by)				->select();		}		is_null($list) && $list = array();		foreach($list as $key=>$row) {			$ts = strtotime($row['examine_time']);			$list[$key]['date'] = date('Y-m-d', $ts);			$list[$key]['time'] = date('H:i', $ts);			$list[$key]['new_title'] = $row['title'] . $row['des'] . $row['weight'] . '处';		}				return array(			'total'=>$total,			'data' => $list		);	}		public function statusToQueryCondition($status, &$data) {		if ($status < 20) {			$data['status_id'] = $status;		}else {			switch($status) {				case 21:					$data['end_date'] = date('Y-m-d');					break;				case 22:					$data['is_vp'] = 1;					break;			}		}	}		public function getIssueDetail($id) {		$cond = array('a.id'=>$id);		$info = M('Issue')->alias('a')			->field('a.*, b.name as status_name, u.username')			->join('issue_status b ON a.status_id=b.status_id')			->join('user u ON a.user_id=u.id')			->where($cond)->find();				if(empty($info)) return array();				$ts = strtotime($info['examine_time']);		$info['date'] = date('Y-m-d', $ts);		$info['time'] = date('H:i', $ts);		!empty($info['img']) && $info['img'] = C('HTTP_SERVER') . $info['img'];		$info['area_text'] = $info['area1'] . $info['area2'] . $info['area3'] . $info['area4'];		$info['target_text'] = $info['target1'] . $info['target2'] . $info['target3'];				return $info;	}		public function getReplyList($issue_id, $type) {		$cond = array('a.issue_id'=>$issue_id);		switch($type) {			case 'text':				$cond['a.type'] = 4;				break;			case 'attachment':				$cond['a.type'] = array('neq', 4);				break;		}				$list = M('IssueReply')->alias('a')			->field('a.*, b.username')			->join('user b ON a.user_id=b.id')			->where($cond)			->select();		if ($list === null) $list = array();		foreach($list as $key=>$row) {			if (!empty($row['path'])) {				$list[$key]['path'] = C('HTTP_SERVER') . $row['path'];				$ext = substr(strrchr($row['path'], '.'), 1);				$img_types = explode('|', C('ALLOW_IMG_TYPE'));				if ($row['type'] ==2 || in_array($ext, $img_types)) {					$list[$key]['is_img'] = 1;				}				$list[$key]['ext'] = $ext;				$list[$key]['fname'] = substr(strrchr($row['path'], '/'), 1);			}		}				return $list;	}		public function canEdit($status_id, $user_type_id) {		if (in_array($status_id, array(1,2)) && in_array($user_type_id, array(10,11,12))) {			return true;		}		/*		if ($status_id == 1 && $user_type_id == 11) {			return true;		}else if($status_id == 2 && $user_type_id == 12) {			return true;		}else if($user_type_id == 10) {			return true;		}		*/				return false;	}		public function canReply($status_id, $user_type_id) {		// this one is for test, should be removed		//if ($user_type_id == 12) return true;				if ($user_type_id < 20) {			return false;		}else if ($status_id == 3 && $user_type_id == 22) {			return false;		}				return true;	}		public function getStatusOperations($status_id, $user_type_id) {		$ret = array(			'code' => 1, // 1 change status, 2 pass or not, 3 can not operate			'data' => array()					);		// for test		/*if ($user_type_id == 12 and $status_id < 3) { // this one is for test, should be removed			$cond = array(				'status_id' => array('in', '4,5,6,7,12')			);			$ret['data'] = M('IssueStatus')->field('*, status_id AS operation_id')->where($cond)->select();		}		else */if ($status_id == 3 && $user_type_id == 21) {			$cond = array(				'status_id' => array('in', '4,5,6,7,12')			);			$ret['data'] = M('IssueStatus')->field('*, status_id AS operation_id')->where($cond)->select();		}else if ($user_type_id == 22) {			if ($status_id == 4) {				$ret['code'] = 2;				$ret['data'] = array(					array('operation_id'=>102, 'name'=>'驳回')				);			}else if(in_array($status_id, array(5,6,7,12))) {				$ret['code'] = 2;				$ret['data'] = array(					array('operation_id'=>101, 'name'=>'同意'),					array('operation_id'=>102, 'name'=>'驳回')				);			}		}else if ($user_type_id == 20) {			if (in_array($status_id, array(12,4,9,10,11))) {				$ret['code'] = 2;				$ret['data'] = array(					//array('operation_id'=>101, 'name'=>'同意'),					array('operation_id'=>102, 'name'=>'驳回')				);			}		}else{			$ret['code'] = 3;		}		return $ret;	}		public function getPassBtns($st, $user_type_id) {		$btns = array();		if ($st == 1 && in_array($user_type_id, array(10, 11, 12))) {			$btns = array(				'pass'		=> array('pass'), 				'nopass'	=> array('nopass')			);		}else if ($st ==2 && in_array($user_type_id, array(10, 12))) {			$btns = array(				'pass'		=> array('pass')			);		}else if ($st == 3 && $user_type_id == 10) {			$btns = array(				'nopass'	=> array('nopass'),			);		}				if (in_array($st, array(3, 21)) && in_array($user_type_id, array(10, 12))) {			$btns['setvp'] = array('setvp');		}				return $btns;	}		public function getTabs($user_type_id) {		$tabs = array(			1	=> array('st'=>1, 'name'=>'待审核'),			2	=> array('st'=>2, 'name'=>'未通过'),			3	=> array('st'=>3, 'name'=>'已通过'),						4	=> array('st'=>4, 'name'=>'已处理'),			5	=> array('st'=>5, 'name'=>'无法处理'),			6	=> array('st'=>6, 'name'=>'非职责范围'),			7	=> array('st'=>7, 'name'=>'非区属范围'),						21	=> array('st'=>21, 'name'=>'超时问题'),			22	=> array('st'=>22, 'name'=>'重点问题'),						//8	=> array('st'=>8, 'name'=>'同意已处理'),			9	=> array('st'=>9, 'name'=>'同意无法处理'),			10	=> array('st'=>10, 'name'=>'同意非职责范围'),			11	=> array('st'=>11, 'name'=>'同意非区属范围'),			12	=> array('st'=>12, 'name'=>'申请延期')					);				if ($user_type_id >= 20) $tabs[3]['name'] = '待处理';				$ids = array();		$all = false;		switch($user_type_id) {			case 10:				$all = true; break;			case 11:				$ids = array(1, 3, 2);				break;			case 12:				$ids = array(1, 3, 2, 4, 9, 10, 11, 21, 22);// rm 8				break;			case 21:				$ids = array(3, 4, 5, 6, 7, 12, 'sp', 21, 22, 9, 10, 11);//rm 8				break;			case 22:				$ids = array(3, 4, 5, 6, 7, 12, 'sp', 21, 22, 9, 10, 11);//rm 8				break;			case 20:				$ids = array(4, 9, 10, 11, 12);				break;		}				if ($all) return $tabs;				$ret = array();		foreach($ids as $v) {			$ret[] = isset($tabs[$v]) ? $tabs[$v] : $v;		}		return $ret;	}		public function doOperation($operation_id, $user_info, $issue_info, $setting) {		$new_status_id = 0;				$cond = array('id'=>$issue_info['id']);		if ( in_array($operation_id, array(4,5,6,7,12)) ) {			$data = array(				'status_id' => $operation_id			);			$new_status_id = $operation_id;			//if ($user_info['user_type_id'] < 20) {			//	$data['last_mod_user_id'] = $user_info['id'];			//}else {				$data['last_mod_user2_id'] = $user_info['id'];			//}			$stat = M('Issue')->where($cond)->data($data)->save();			if (false === $stat) return false;		}else if ( in_array($operation_id, array(101, 102)) ) {			if ($operation_id == 101) { // agree				$opt_res_cfg = array(					4	=> 8,					5	=> 9,					6	=> 10,					7	=> 11,					12	=> 3				);				if ( isset($opt_res_cfg[$issue_info['status_id']]) ) {					$data = array(						'status_id' => $opt_res_cfg[$issue_info['status_id']],						'last_mod_user2_id' => $user_info['id']					);					$new_status_id = $opt_res_cfg[$issue_info['status_id']];					if ($issue_info['status_id'] == 12) {						if (!empty($issue_info['end_date']) && !empty($setting['config_exp_days'])) {							$data['end_date'] = date('Y-m-d', strtotime($issue_info['end_date'] + 3600 * 24 * $setting['config_exp_days']));						}					}					$stat = M('Issue')->where($cond)->data($data)->save();					if (false === $stat) return false;				}			}else if ($operation_id == 102) { // reject				$opt_res_cfg = array(					4	=> 3,					5	=> 3,					6	=> 3,					7	=> 3,					8	=> 3,					9	=> 3,					10	=> 3,					11	=> 3,					12	=> 3				);				if ( isset($opt_res_cfg[$issue_info['status_id']]) ) {					$data = array(						'status_id' => $opt_res_cfg[$issue_info['status_id']],						'last_mod_user2_id' => $user_info['id']					);					$new_status_id = $opt_res_cfg[$issue_info['status_id']];										$stat = M('Issue')->where($cond)->data($data)->save();					if (false === $stat) return false;				}			}		}				return $new_status_id;	}		public function importOneRow($data) {		$err_msg = array(			1	=> '区域/类别不能为空',			2	=> '道路不能为空',			3	=> '区/类别/街/路不存在',			4	=> '指标代码错误',			5	=> '指标代码和指标名称不能都为空',			6	=> '指标不存在',			7	=> '插入数据库失败',			8	=> '录入员不能为空',			9	=> '发现人不能为空',			10	=> '录入员用户名不存在',			11	=> '指标代码不能为空',			12	=> '指定的图片不存在'		);		$ret = array(			'code' => 0,			'error'	=> ''		);				if (empty($data['area1']) || empty($data['area2'])) {			$ret['code'] = 1;		}				if ($ret['code'] == 0) {			if (empty($data['checker'])) {				$ret['code'] = 9;			}		}				if ($ret['code'] == 0) {			if (empty($data['target_code'])) {				$ret['code'] = 11;			}		}				/*		if ($ret['code'] == 0) {			if (empty($data['username'])) {				$ret['code'] = 8;			}else {				$user = M('User')->where(array('username'=>$data['username']))->find();				if (empty($user)) {					$ret['code'] = 10;				}else {					$data['user_id'] = $user['id'];				}			}		}*/				if ($ret['code'] == 0) {			$area = $data['area4']; //!empty($data['area4']) ? $data['area4'] : (!empty($data['area3']) ? $data['area3'] : '');			if (empty($area)) {				$ret['code'] = 2;			}else if (empty($data['area3'])) {				$data['area3'] = $data['area4'];				$data['area4'] = '';			}		}				$AreaEvent = A('Area', 'Event');		if ($ret['code'] == 0) {			$area = $AreaEvent->getLeafAreaByNames($data['city_id'], $data['area1'], $data['area2'], $data['area3'], $data['area4']);			if (!$area) {				$ret['code'] = 3;			}else {				$data['area_id'] = $area['id'];			}		}				if ($ret['code'] == 0) {			$TargetEvent = A('Target', 'Event');			if (!empty($data['target_code'])) {				$target = $TargetEvent->getTargetByCode($data['city_id'], $data['target_code']);				if (!$target) {					$ret['code'] = 4;				}else {					if ($target['path'] == ',0,') {						$data['target1'] = $target['name'];						$data['target2'] = $target['target3'] = '';					}else {						$ids = str_replace(',0,', '', $target['path']);						$ids = substr($ids, 0, strlen($ids)-1);						$targets = $TargetEvent->getTargetsByIds($ids);						if (empty($targets)) {							$ret['code'] = 4;						}else {							$n = 1;							foreach($targets as $k=>$t) {								if ($k >= 2) break;								$data['target' . ($n++)] = $t['name'];							}							$data['target' . $n] = $target['name'];						}					}					$data['target_id'] = $target['id'];				}			}else { // target code is empty, check target1, target2, target3				if (empty($data['target1'])) {					$ret['code'] = 5;				}else {					$target = $TargetEvent->getLeafTargetByNames($data['city_id'], $data['target1'], $data['target2'], $data['target3']);					if (!$target) {						$ret['code'] = 6;					}else {						$data['target_code'] = $target['code'];						$data['target_id'] = $target['id'];					}				}			}		}				if ($ret['code'] == 0) {			if(!file_exists(C('UPLOAD_DIR') . $data['img'])) {				$ret['code'] = 12;			}		}				if ($ret['code'] == 0) {			$data['status_id'] = 1;			!empty($data['img']) && $data['img'] = C('UPLOAD_DIR') . $data['img'];			$data['tags'] = $data['area1'] . '|';			$data['tags'] .= $data['area2'] . '|';			$data['tags'] .= $data['area3'] . '|';			!empty($data['area4']) && $data['tags'] .= $data['area4'] . '|';			$data['tags'] .= $data['target1'] . '|';			!empty($data['target2']) && $data['tags'] .= $data['target2'] . '|';			!empty($data['target3']) && $data['tags'] .= $data['target3'] . '|';			$data['tags'] .= $data['target_code'] . '|';			$data['title'] = $data['area1'] . $data['area2'] . $data['area3'] . $data['area4'] . $data['target1'] . $data['target2'] . $data['target3'];			if (isset($data['come_from']) && empty($data['come_from']) ) unset($data['come_from']);			$data['create_time'] = date('Y-m-d H:i:s');			//print_r($data);			$id = M('Issue')->data($data)->add();			if (empty($id)) {				$ret['code'] = 7;			}else {				// add to issue_replay table				$reply_data = array(					'issue_id' 	=> $id,					'type'		=> 1,					'path'		=> $data['img'],					'user_id'	=> $data['user_id'],					'create_time'	=> date('Y-m-d H:i:s')				);				M('IssueReply')->data($reply_data)->add();			}		}				if ($ret['code'] > 0) $ret['error'] = $err_msg[$ret['code']];				return $ret;	}		public function getStatusName($status_id, $user_type_id, $status_name) {		if ($user_type_id > 20 && $status_id == 3) {			$status_name = '待处理';		}		return $status_name;	}}