<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends GlobalController {
    public function index(){
		$this->assign('title', '问题管理');
		$this->display();
    }
}