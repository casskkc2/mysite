<?php
namespace Home\Controller;
use Think\Controller;
class BaseController extends Controller {
	protected $setting = array();
	
	function _initialize(){//var_dump(CONTROLLER_NAME);var_dump(ACTION_NAME);exit;
		$setting = M('Setting')->select();
		foreach($setting as $row) {
			if ($row['serialized'] == 1) {
				$this->setting[$row['key']] = unserialize($row['value']);
			}else{
				$this->setting[$row['key']] = $row['value'];
			}
		}
	}
}