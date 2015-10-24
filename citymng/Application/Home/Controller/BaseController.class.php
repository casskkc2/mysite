<?php
namespace Home\Controller;
use Think\Controller;
class BaseController extends Controller {
	protected $setting = array();
	
	function _initialize(){//var_dump(CONTROLLER_NAME);var_dump(ACTION_NAME);exit;
		
		/*$bulletin = M('Bulletin')->find();
		if (!empty($bulletin)) {
			$this->assign('bulletin', $bulletin['content']);
		}*/
		$this->assign('bulletin', '');
		
		$logged = 0;
		if (session('?user')) {
			$this->assign('current_user', session('user'));
			$logged = 1;
		}
		$this->assign('logged', $logged);
	}
	
	protected function generateDataForDataGrid($total, $data) {
		return json_encode(array(
			'rows'=>(empty($data) ? array() : $data), 
			'total'=>$total
		));
	}
}