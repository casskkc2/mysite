<?phpnamespace Home\Event;use Think\Controller;class AreaEvent extends Controller {		public function getProvinceList() {		$list = M('Province')->select();				return $list;	}		public function getCityList($province_id) {		$list = M('City')->where(array('father'=>$province_id))->select();				return $list;	}		public function getAreaList($city_id, $pid) {		$cond = array(			'city_id' => $city_id,			'path' => array('like', '%,'.$pid.','),		);		$list = M('Area')->where($cond)->order('sort')->select();		return $list;	}}