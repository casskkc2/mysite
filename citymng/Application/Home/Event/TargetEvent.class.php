<?phpnamespace Home\Event;use Think\Controller;class TargetEvent extends Controller {		public function getTargetList($city_id, $pid) {		$cond = array(			'city_id' => $city_id,			'path' => array('like', '%,'.$pid.','),		);		$list = M('Target')->where($cond)->order('sort')->select();		return $list;	}		public function getTargetByCode($city_id, $code) {		$cond = array(			'city_id' => $city_id,			'code' => $code		);		$info = M('Target')->where($cond)->find();		if(empty($info)) return false;				return $info;	}		public function getTargetsByIds($ids) {		$cond = array(			'id' => array('in', $ids)		);		$list = M('Target')->where($cond)->select();		return $list;	}		public function getLeafTargetByNames($city_id, $target1, $target2, $target3) {		$cond = array(			'city_id'	=> $city_id		);				$where = $cond;		$where['name'] = $target1;		$where['path'] = ',0,';		$info = M('Target')->where($where)->find();		if (empty($info)) return false;				if (empty($target2)) return $info;				$where = $cond;		$where['name'] = $target2;		$where['path'] = $info['path'] . $info['id'] . ',';		$info = M('Target')->where($where)->find();		if (empty($info)) return false;				if (empty($target3)) return $info;				$where = $cond;		$where['name'] = $target3;		$where['path'] = $info['path'] . $info['id'] . ',';		$info = M('Target')->where($where)->find();		if (empty($info)) return false;				return $info;	}}