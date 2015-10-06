<?php
namespace Home\Controller;
use Think\Controller;
class SettingController extends GlobalController {
	function _initialize(){
		parent::_initialize();
		if ($this->user['user_type_id'] != 10) {
			$this->error('无权限访问');
		}
	}
    public function edit(){
		if ($_POST) {
			//dump($_POST);exit;
			$setting_keys = C('CONFIG_SETTING');
			foreach($_POST as $key=>$value) {
				if (isset($setting_keys[$key])) {
					$info = M('Setting')->where(array('key'=>$key))->find();
					if (empty($info)) {
						$data = array(
							'key'	=> $key,
							'value'	=> $value
						);
						M('Setting')->data($data)->add();
					}else {
						$data = array(
							'id'	=> $info['id'],
							'value'	=> $value
						);
						M('Setting')->data($data)->save();
					}
				}
			}
			$this->success('修改成功');
		}
		
		$setting_keys = C('CONFIG_SETTING');
		foreach($setting_keys as $key=>$value) {
			$setting_keys[$key]['value'] = '';
		}
		$list = M('Setting')->select();
		$settings = array();
		foreach($list as $row) {
			if (isset($setting_keys[$row['key']])) {
				$setting_keys[$row['key']]['value'] = $row['value'];
			}
		}
		$this->assign('setting_keys', $setting_keys);
		
		$this->assign('title', '配置项');
		$this->display();
    }
}