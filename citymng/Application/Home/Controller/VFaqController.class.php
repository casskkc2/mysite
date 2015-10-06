<?php
namespace Home\Controller;
use Think\Controller;
class VFaqController extends BaseController {	
	public function index() {
		$list = M('Faq')->order('sort')->select();
		
		$this->assign('list', $list);
		$this->assign('title', '常见问题');
		$this->display();
	}
}