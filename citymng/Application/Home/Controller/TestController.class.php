<?php
namespace Home\Controller;
use Think\Controller;
class TestController extends Controller {
	public function test() {
		//$list = M('Bulletin')->select();
		$list = M('User')->select();
		dump($list);
	}
}