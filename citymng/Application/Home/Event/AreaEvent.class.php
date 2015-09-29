<?phpnamespace Home\Event;use Think\Controller;class AreaEvent extends Controller {		public function getProvinceList() {		$list = M('Province')->select();				return $list;	}		public function getCityList($province_id) {		$list = M('City')->where(array('father'=>$province_id))->select();				return $list;	}		public function getAreaList($city_id, $pid) {		$cond = array(			'city_id' => $city_id,			'path' => array('like', '%,'.$pid.','),		);		$list = M('Area')->where($cond)->order('sort')->select();		return $list;	}		public function getAreaAndChildrenByIds($city_id, $ids) {		$path_cond = array();		foreach($ids as $id) {			$path_cond[] = array('like', '%,' . $id . ',%');		}		if (!empty($path_cond)) {			$path_cond[] = 'or';		}		$where = array(			'id' 	=> array('in', $ids),			'path'	=> $path_cond,			'_logic'=> 'or'		);				$cond = array(			'city_id' => $city_id,			'_complex' => $where		);		$list = M('Area')->where($cond)->order('sort')->select();		return $list;	}		public function getLeafAreaByNames($city_id, $area1, $area2, $area3, $area4) {		$cond = array(			'city_id'	=> $city_id		);				$where = $cond;		$where['name'] = $area1;		$where['path'] = ',0,';		$info = M('Area')->where($where)->find();		if (empty($info)) return false;				$where = $cond;		$where['name'] = $area2;		$where['path'] = $info['path'] . $info['id'] . ',';		$info = M('Area')->where($where)->find();		if (empty($info)) return false;				$where = $cond;		$where['name'] = $area3;		$where['path'] = $info['path'] . $info['id'] . ',';		$info = M('Area')->where($where)->find();		if (empty($info)) return false;				if (empty($area4)) return $info;				$where = $cond;		$where['name'] = $area4;		$where['path'] = $info['path'] . $info['id'] . ',';		$info = M('Area')->where($where)->find();		if (empty($info)) return false;				return $info;	}}